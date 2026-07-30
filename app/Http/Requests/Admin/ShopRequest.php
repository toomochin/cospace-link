<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSystemAdmin() === true;
    }

    public function rules(): array
    {
        $shop = $this->route('shop');
        $ownerId = $shop?->owners()->value('users.id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'area_name' => ['required', 'string', 'max:100'],
            'owner_email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($ownerId),
            ],
        ];
    }
}
