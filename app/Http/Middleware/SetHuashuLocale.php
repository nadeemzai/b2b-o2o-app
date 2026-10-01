<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Huashu panel locale middleware.
 * Reads locale from session (set by the language switcher).
 * Defaults to zh_CN so the panel opens in Chinese for Huashu staff.
 * NOTE: must sit AFTER StartSession in the middleware stack.
 */
class SetHuashuLocale
{
    protected const SUPPORTED = ['en', 'zh_CN'];
    protected const DEFAULT   = 'zh_CN';

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Explicit session value (set by the language switcher POST)
        $locale = session('locale');

        // 2. Cookie fallback (belt-and-suspenders across redirects)
        if (! $locale) {
            $locale = $request->cookie('huashu_locale');
        }

        // 3. Default to Chinese
        if (! $locale || ! in_array($locale, self::SUPPORTED, true)) {
            $locale = self::DEFAULT;
        }

        App::setLocale($locale);

        return $next($request);
    }
}
