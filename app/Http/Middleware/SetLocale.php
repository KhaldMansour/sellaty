<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->query('locale');
        in_array($locale, ['en', 'ar']) ? App::setLocale($locale) : App::setLocale('ar');

        return $next($request);
    }
}
