<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationStoreRequest extends FormRequest
{
    /**
     * リクエストの実行権限（ログイン済みユーザーのみ許可）
     */
    public function authorize(): bool
    {
        return true; // ルーティングの auth ミドルウェアで保護されているため true
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'payment_type' => ['sometimes', 'required', 'in:credit_card,onsite,free'],
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'start_time.required' => '開始日時を選択してください。',
            'start_time.after' => '過去の日時は予約できません。',
            'end_time.required' => '終了日時を選択してください。',
            'end_time.after' => '終了日時は開始日時より後の時間を指定してください。',
            'payment_type.required' => 'お支払い方法を選択してください。', // ★ 追加
            'payment_type.in' => '無効なお支払い方法が選択されました。', // ★ 追加
        ];
    }
}
