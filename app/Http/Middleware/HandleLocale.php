<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * B6 — the interface language is chosen per browser: the `locale` cookie the
 * language switcher writes, when it names a supported locale, else the
 * configured default. The cookie is unencrypted (bootstrap/app.php) because
 * the browser writes it.
 */
final class HandleLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $wanted = $request->cookie('locale');

        /** @var list<string> $supported */
        $supported = config('app.locales');

        if (is_string($wanted) && in_array($wanted, $supported, true)) {
            App::setLocale($wanted);
        }

        return $next($request);
    }
}
