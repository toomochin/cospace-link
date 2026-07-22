<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * リクエストの実行権限（ログインユーザーのみ許可）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'password' => ['nullable', 'string', Password::min(8), 'confirmed'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    /**
     * エラーメッセージの日本語化（要件定義書 FN004 / FN026 に準拠）
     */
    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'email.unique' => 'このメールアドレスは既に登録されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワードと一致しません。',
            'profile_image.image' => '指定されたファイルが画像ではありません。',
            'profile_image.mimes' => '画像は jpeg, png, jpg 形式でアップロードしてください。',
            'profile_image.max' => '画像サイズは 2MB 以内にしてください。',
        ];
    }
}