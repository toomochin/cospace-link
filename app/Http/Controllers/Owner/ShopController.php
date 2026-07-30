<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ShopRequest;
use App\Support\AmenityNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    public function edit(Request $request)
    {
        return view('owner.shop.edit', ['shop' => $request->user()->shop]);
    }

    public function update(ShopRequest $request)
    {
        $shop = $request->user()->shop;
        $data = $request->validated();
        $oldImagePath = $shop->image_path;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('shops', 'public');
        }

        unset($data['image']);
        $data['amenities'] = AmenityNormalizer::normalize($data['amenities'] ?? []);
        $shop->update($data);

        if (isset($data['image_path']) && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()->route('owner.shop.edit')->with('status', '店舗情報を更新しました。');
    }
}
