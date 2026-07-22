<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated(); // 検証済みデータを取得

        // 1. プロフィール画像のアップロード処理
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image_path) {
                Storage::disk('public')->delete($user->profile_image_path);
            }
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image_path = $path;
        }

        // 2. メールアドレス変更時の処理（再認証フラグのリセット）
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
            $user->sendEmailVerificationNotification();
        }

        // 3. 名前の更新
        $user->name = $validated['name'];

        // 4. パスワード更新（入力がある場合のみ）
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'プロフィールを更新しました！');
    }
}