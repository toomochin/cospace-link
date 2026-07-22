<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FacilityStoreRequest;
use App\Models\Facility;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('id', 'asc')->get();
        return view('admin.facilities.index', compact('facilities'));
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