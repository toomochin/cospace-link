<?php

namespace App\Http\Requests\Owner;

use App\Support\AmenityNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class ShopRequest extends FormRequest
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
            'area_name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'access' => ['nullable', 'string', 'max:2000'],
            'opening_hours' => ['required', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d-(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amenities' => ['nullable', 'array', 'max:20'],
            'amenities.*' => ['string', 'max:100', 'distinct'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}
