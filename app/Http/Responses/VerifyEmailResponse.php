<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Illuminate\Http\RedirectResponse;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        // intended を上書きし、強制的にプロフィール編集画面へ遷移させる
        return redirect()->route('profile.edit')
            ->with('status', 'メール認証が完了しました。プロフィールの設定を行ってください。');
    }
}