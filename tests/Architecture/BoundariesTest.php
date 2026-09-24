<?php

declare(strict_types=1);

/*
 * Architecture boundaries, enforced rather than documented.
 *
 * The one rule that matters most: App\Domain is where the requirements' rules
 * live, and it must stay reachable without booting Laravel. That is what lets
 * the rules run as fast unit tests over large datasets, and what stops a
 * calculation rule from quietly acquiring a database or a request object.
 *
 * These run as part of the normal suite (`composer test`). The rules for the
 * HTTP layer (controllers, services, Ports) are in AreaBoundariesTest.php.
 */

arch('the domain layer does not reach for the framework')
    ->expect('App\Domain')
    ->not->toUse([
        'Illuminate\Database',
        'Illuminate\Http',
        'Illuminate\Support\Facades',
        'Inertia\Inertia',
        'App\Http',
        'App\Models',
        'App\Spec',
    ]);

arch('the requirement tooling stays independent of the product code')
    ->expect('App\Spec')
    ->not->toUse([
        'App\Domain',
        'App\Http',
        'App\Models',
    ]);

arch('domain and tooling classes are final')
    ->expect(['App\Domain', 'App\Spec'])
    ->classes()
    ->toBeFinal();

arch('domain and tooling files declare strict types')
    ->expect(['App\Domain', 'App\Spec'])
    ->toUseStrictTypes();

arch('no debug helper survives into the repository')
    ->expect(['dd', 'dump', 'ddd', 'ray', 'var_dump', 'print_r', 'die', 'exit'])
    ->not->toBeUsed();
