<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FacilityStoreRequest extends FormRequest
{
    /**
     * リクエストの実行権限
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前のデータ整頓（チェックボックスの処理）
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'price_per_30min' => ['required', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages(): array
    {
        return [
            'name.required' => '施設名を入力してください。',
            'type.required' => '種別を選択してください。',
            'price_per_30min.required' => '30分あたりの料金を入力してください。',
            'price_per_30min.min' => '料金は0円以上で入力してください。',
            'capacity.required' => '定員を入力してください。',
            'capacity.min' => '定員は1名以上で指定してください。',
        ];
    }
}