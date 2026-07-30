<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->isShopOwner() && $request->user()->shop_id !== null,
            403,
            '店舗管理者権限が必要です。',
        );

        return $next($request);
    }
}
