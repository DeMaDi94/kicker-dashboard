<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

// B12 — `/` is not a page of its own.
it('sends / to the dashboard', function () {
    $this->get(route('home'))->assertRedirect('/dashboard');
});

it('sends a signed-out visitor on to the login', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});

it('serves the primitive gallery outside production', function () {
    $this->actingAs(User::factory()->create())
        ->get('/_primitives')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('primitives/index'));
});
