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
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['nullable', 'required_with:end_time', 'date_format:H:i'],
            'end_time' => ['nullable', 'required_with:start_time', 'date_format:H:i', 'after:start_time'],
            'amenities' => ['nullable', 'array', 'max:20'],
            'amenities.*' => ['string', 'max:100', 'distinct'],
            'keyword' => ['nullable', 'string', 'max:100'],
        ];
    }
}
