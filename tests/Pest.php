<?php

use App\Domain\Users\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * A user holding the admin role (B13).
 *
 * @param  array<string, mixed>  $attributes
 */
function admin(array $attributes = []): User
{
    return User::factory()->withRole(Role::Admin)->create($attributes);
}

/**
 * A user holding the plain user role (B13).
 *
 * @param  array<string, mixed>  $attributes
 */
function member(array $attributes = []): User
{
    return User::factory()->withRole(Role::User)->create($attributes);
}

/**
 * A golden-vector dataset: `tests/Fixtures/{name}.json`, shaped
 * `{"cases": [{…}, …]}` — reference answers the business already trusts,
 * frozen so a rule is checked against them rather than against invented
 * inputs (.claude/rules/testing.md). Each case reaches the test as one array,
 * so the fixture can grow a field without touching every signature.
 *
 *     it('matches the reference rounding', function (array $case) { … })
 *         ->with(goldenVectors('rounding'));
 *
 * @return array<int, array{array<string, mixed>}>
 */
function goldenVectors(string $name): array
{
    $file = __DIR__."/Fixtures/{$name}.json";

    /** @var array{cases: list<array<string, mixed>>} $vectors */
    $vectors = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

    return array_map(static fn (array $case): array => [$case], $vectors['cases']);
}
