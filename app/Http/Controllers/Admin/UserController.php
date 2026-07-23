<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 登録会員一覧の表示
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * アカウント利用ステータス (is_active) の切り替え
     */
    public function toggleStatus(Request $request, User $user)
    {
        // 自分自身のアカウント無効化を防止
        if ($user->id === $request->user()->id) {
            return back()->with('error', '自分自身のアカウントを無効化することはできません。');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? '有効' : '利用停止';

        return back()->with('status', "ユーザー「{$user->name}」のアカウントを【{$statusText}】に変更しました。");
    }
}