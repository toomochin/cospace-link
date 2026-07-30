<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $route = match ($request->user()?->role) {
            'shop_owner' => 'owner.dashboard',
            'system_admin' => 'admin.dashboard',
            default => 'home',
        };

        return redirect()->route($route);
    }
}
