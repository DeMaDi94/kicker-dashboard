<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * D16 — while outgoing mail is switched off, the routes whose only purpose is
 * to send a mail do not exist. Fortify registers most of them itself, so they
 * are named here rather than guarded where they are declared.
 */
final class RefuseMailWhenOff
{
    private const ROUTES = [
        'password.request',
        'password.email',
        'verification.send',
        'users.password-reset-link',
    ];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(...self::ROUTES) && ! Setting::mailEnabled()) {
            abort(404);
        }

        return $next($request);
    }
}
