<?php

namespace App\Http\Requests;

use App\Support\AmenityNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilitySearchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('amenities'))) {
            $this->merge(['amenities' => AmenityNormalizer::normalize($this->input('amenities'))]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['meeting_room', 'area'])],
            'date' => ['nullable', 'required_with:start_time,end_time', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['nullable', 'required_with:date,end_time', 'date_format:H:i'],
            'end_time' => ['nullable', 'required_with:date,start_time', 'date_format:H:i', 'after:start_time'],
            'amenities' => ['nullable', 'array', 'max:20'],
            'amenities.*' => ['string', 'max:100', 'distinct'],
            'keyword' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required_with' => '時間で検索する場合は利用日を入力してください。',
            'start_time.required_with' => '利用日・終了時間を指定する場合は開始時間を入力してください。',
            'end_time.required_with' => '利用日・開始時間を指定する場合は終了時間を入力してください。',
            'end_time.after' => '終了時間は開始時間より後にしてください。',
        ];
    }
}
