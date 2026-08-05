<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;

/**
 * 加盟店舗の掲載状態を切り替える単一アクションを担当する。
 */
class ToggleShopStatusController extends Controller
{
    public function __invoke(Shop $shop)
    {
        $shop->update(['is_active' => ! $shop->is_active]);

        $message = $shop->is_active
            ? '加盟店舗を再掲載しました。'
            : '加盟店舗を掲載停止にしました。';

        return redirect()->route('admin.shops.index')->with('status', $message);
    }
}
