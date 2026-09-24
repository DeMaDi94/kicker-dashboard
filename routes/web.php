<?php

use Illuminate\Support\Facades\Route;

// B12 — no landing page: signed in, `/` is the dashboard; signed out, the
// dashboard's auth middleware sends the visitor on to the login.
Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    /*
     * The primitive gallery: every components/core primitive in its states,
     * inside the real shell. It carries no data and is never registered in
     * production.
     */
    if (! app()->isProduction()) {
        Route::inertia('_primitives', 'primitives/index')->name('primitives');
    }
});

require __DIR__.'/settings.php';
