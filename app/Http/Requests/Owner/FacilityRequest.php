<?php

namespace App\Http\Requests\Owner;

use App\Support\AmenityNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_array($this->input('amenities'))) {
            $this->merge(['amenities' => AmenityNormalizer::normalize($this->input('amenities'))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isShopOwner() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['meeting_room', 'area'])],
            'price_per_30min' => ['required', 'integer', 'min:0', 'max:1000000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10000'],
            'equipment' => ['nullable', 'string', 'max:1000'],
            'amenities' => ['nullable', 'array', 'max:20'],
            'amenities.*' => ['string', 'max:100', 'distinct'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
