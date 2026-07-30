<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopRequest;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::query()->with('owners')->withCount('facilities')->orderBy('id')->get();

        return view('admin.shops.index', compact('shops'));
    }

    public function create()
    {
        return view('admin.shops.create');
    }

    public function store(ShopRequest $request)
    {
        $data = $request->validated();
        $ownerEmail = $data['owner_email'];
        unset($data['owner_email']);

        DB::transaction(function () use ($data, $ownerEmail): void {
            $shop = Shop::query()->create($data + [
                'address' => '未設定',
                'opening_hours' => '09:00-21:00',
                'amenities' => [],
                'is_active' => true,
            ]);

            User::query()->create([
                'name' => "{$shop->name} 店舗管理者",
                'email' => $ownerEmail,
                'password' => Str::password(32),
                'role' => 'shop_owner',
                'shop_id' => $shop->id,
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        });

        try {
            $status = Password::sendResetLink(['email' => $ownerEmail]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.shops.index')->with(
                'warning',
                '加盟店舗は登録されましたが、招待メールを送信できませんでした。メール設定を確認して再送してください。',
            );
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()->route('admin.shops.index')->with(
                'warning',
                '加盟店舗は登録されましたが、招待メールを送信できませんでした。',
            );
        }

        return redirect()->route('admin.shops.index')
            ->with('status', '加盟店舗を登録し、店舗管理者へパスワード設定メールを送信しました。');
    }

    public function edit(Shop $shop)
    {
        $shop->load('owners');

        return view('admin.shops.edit', compact('shop'));
    }

    public function update(ShopRequest $request, Shop $shop)
    {
        $data = $request->validated();
        $ownerEmail = $data['owner_email'];
        unset($data['owner_email']);

        DB::transaction(function () use ($shop, $data, $ownerEmail): void {
            $shop->update($data);
            $shop->owners()->firstOrFail()->update(['email' => $ownerEmail]);
        });

        return redirect()->route('admin.shops.index')->with('status', '加盟店舗を更新しました。');
    }

    public function destroy(Shop $shop)
    {
        $shop->update(['is_active' => false]);

        return redirect()->route('admin.shops.index')->with('status', '加盟店舗を掲載停止にしました。');
    }
}
