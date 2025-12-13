<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // لو كان الطلب JSON (API) يرجع رسالة JSON
        if ($request->expectsJson()) {
            abort(response()->json(['message' => 'Unauthenticated.'], 401));
        }

        // لو واجهة ويب، اعيد المسار لتسجيل الدخول
        return route('login');
    }
}
