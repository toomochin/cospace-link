<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * プロフィール編集画面表示
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * プロフィール更新処理
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. バリデーション（nameとprofile_imageのみにする）
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // 2. プロフィール画像のアップロード処理
        if ($request->hasFile('profile_image')) {
            // 既存画像があればストレージから削除
            if ($user->profile_image_path) {
                Storage::disk('public')->delete($user->profile_image_path);
            }
            // 新しい画像を storage/app/public/profiles に保存
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image_path = $path;
        }

        // 3. データの更新（postcode, building は削除）
        $user->name = $validated['name'];
        $user->save();

        // 4. 更新完了後、トップ画面（home）へリダイレクト
        return redirect()->route('home')->with('status', 'プロフィールを更新しました！');
    }
}