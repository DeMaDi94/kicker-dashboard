<?php

declare(strict_types=1);

namespace App\Http\Visits\CountVisit;

use App\Domain\Visits\PublicPage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function Illuminate\Support\defer;

/**
 * VIS-01, D14 — counts a public page's visit: a successful (200) GET by a
 * guest, after the response is sent. An Inertia prefetch or partial reload is
 * not a visit. Sets no cookie (VIS-02).
 */
final class CountVisitMiddleware
{
    public function __construct(private CountVisitService $count) {}

    public function handle(Request $request, Closure $next, string $page): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->isMethod('GET')
            && $response->getStatusCode() === Response::HTTP_OK
            && $request->user() === null
            && $request->header('Purpose') !== 'prefetch'
            && ! $request->hasHeader('X-Inertia-Partial-Component')) {
            $ip = $request->ip();
            $userAgent = $request->userAgent();

            defer(fn () => ($this->count)(PublicPage::from($page), $ip, $userAgent));
        }

        return $response;
    }
}
