<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('admin_authenticated')) {
            return redirect('/login');
        }

        return $next($request);
    }
}
