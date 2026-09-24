<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

/*
 * B6 — the interface language comes from the `locale` cookie when it names a
 * supported locale, else from the configured default, and the page receives
 * that locale's catalogue.
 */

beforeEach(function () {
    config(['app.locale' => 'de', 'app.locales' => ['de', 'en']]);
});

it('serves the default locale and its catalogue without a cookie', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('locale', 'de')
            ->where('locales', ['de', 'en'])
            ->where('i18n.Log out', 'Abmelden'));
});

it('switches to the locale the cookie names', function () {
    $this->actingAs(User::factory()->create())
        ->withUnencryptedCookie('locale', 'en')
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('locale', 'en')
            ->where('i18n', []));
});

it('ignores a cookie naming a locale that is not supported', function () {
    $this->actingAs(User::factory()->create())
        ->withUnencryptedCookie('locale', 'xx')
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('locale', 'de'));
});

it('translates server-side messages too', function () {
    $this->withUnencryptedCookie('locale', 'de')
        ->post(route('login.store'), ['email' => 'nobody@example.com', 'password' => 'wrong'])
        ->assertSessionHasErrors(['email' => __('auth.failed', [], 'de')]);

    expect(__('auth.failed', [], 'de'))->toBe('E-Mail-Adresse oder Passwort ist falsch.');
});
