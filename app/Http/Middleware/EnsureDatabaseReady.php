<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureDatabaseReady
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isDatabaseReady()) {
            return $next($request);
        }

        if ($request->routeIs('login', 'login.store', 'register', 'register.store', 'landing')) {
            return $next($request);
        }

        Auth::logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->with('error', 'Database is not initialized yet. Run "php artisan migrate --seed" and reload the page.');
    }

    private function isDatabaseReady(): bool
    {
        try {
            return Schema::hasTable('users') && Schema::hasTable('sections');
        } catch (Throwable) {
            return false;
        }
    }
}
