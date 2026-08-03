<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityStoreRequest;
use App\Models\Facility;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'area_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['meeting_room', 'area'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'order' => ['nullable', Rule::in([
                'id_asc', 'id_desc', 'shop_asc', 'area_asc', 'name_asc', 'name_desc',
                'price_asc', 'price_desc', 'capacity_asc', 'capacity_desc', 'status_desc',
            ])],
        ]);

        $query = Facility::query()
            ->with('shop:id,name,area_name')
            ->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('shop_id', $shopId))
            ->when($filters['area_name'] ?? null, function ($query, $areaName): void {
                $query->whereHas('shop', fn ($shopQuery) => $shopQuery->where('area_name', $areaName));
            })
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when(isset($filters['status']), function ($query) use ($filters): void {
                $query->where('is_active', $filters['status'] === 'active');
            });

        [$sort, $direction] = match ($filters['order'] ?? 'id_asc') {
            'id_desc' => ['id', 'desc'],
            'shop_asc' => ['shop', 'asc'],
            'area_asc' => ['area', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'price_asc' => ['price_per_30min', 'asc'],
            'price_desc' => ['price_per_30min', 'desc'],
            'capacity_asc' => ['capacity', 'asc'],
            'capacity_desc' => ['capacity', 'desc'],
            'status_desc' => ['is_active', 'desc'],
            default => ['id', 'asc'],
        };

        $sortColumn = match ($sort) {
            'shop' => Shop::query()->select('name')->whereColumn('shops.id', 'facilities.shop_id')->limit(1),
            'area' => Shop::query()->select('area_name')->whereColumn('shops.id', 'facilities.shop_id')->limit(1),
            default => $sort,
        };

        $facilities = $query->orderBy($sortColumn, $direction)->orderBy('facilities.id')->get();
        $shops = Shop::query()->orderBy('name')->get(['id', 'name']);
        $areas = Shop::query()->select('area_name')->distinct()->orderBy('area_name')->pluck('area_name');

        return view('admin.facilities.index', compact('facilities', 'shops', 'areas', 'filters'));
    }
    public function create()
    {
        return view('admin.facilities.create');
    }

    public function store(FacilityStoreRequest $request)
    {
        // バリデーション済みのデータ取得
        $data = $request->validated();

        // 画像のアップロード処理
        if ($request->hasFile('image')) {
            // storage/app/public/facilities に保存
            $path = $request->file('image')->store('facilities', 'public');
            $data['image_path'] = $path;
        }

        Facility::create($data);

        return redirect()->route('admin.facilities.index')->with('status', '施設を追加しました。');
    }

    public function edit($id)
    {
        $facility = Facility::findOrFail($id);
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(FacilityStoreRequest $request, $id)
    {
        $facility = Facility::findOrFail($id);
        $data = $request->validated();

        // 新しい画像がアップロードされた場合
        if ($request->hasFile('image')) {
            // 既存の画像ファイルがあれば削除
            if ($facility->image_path && Storage::disk('public')->exists($facility->image_path)) {
                Storage::disk('public')->delete($facility->image_path);
            }

            // 新しい画像を保存
            $path = $request->file('image')->store('facilities', 'public');
            $data['image_path'] = $path;
        }

        $facility->update($data);

        return redirect()->route('admin.facilities.index')->with('status', '施設情報を更新しました。');
    }
}