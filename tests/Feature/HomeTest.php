<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

// D1 — `/` is the public season view, not a redirect to a dashboard.
it('shows the season view at / without signing in', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('seasons/show'));
});

it('serves the primitive gallery outside production', function () {
    $this->actingAs(User::factory()->create())
        ->get('/_primitives')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('primitives/index'));
});
