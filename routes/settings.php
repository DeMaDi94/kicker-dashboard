<?php

use App\Domain\Users\Permission;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Users\CreateUser\CreateUserController;
use App\Http\Users\DeleteUser\DeleteUserController;
use App\Http\Users\EditUser\EditUserController;
use App\Http\Users\ListUsers\ListUsersController;
use App\Http\Users\RestoreUser\RestoreUserController;
use App\Http\Users\SendPasswordResetLink\SendPasswordResetLinkController;
use App\Http\Users\StoreUser\StoreUserController;
use App\Http\Users\UpdateUser\UpdateUserController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    // B13 / B16 — user management, gated by permission, never by role name.
    Route::get('settings/users', ListUsersController::class)
        ->can(Permission::ViewUsers->value)->name('users.index');
    Route::get('settings/users/create', CreateUserController::class)
        ->can(Permission::CreateUsers->value)->name('users.create');
    Route::post('settings/users', StoreUserController::class)
        ->can(Permission::CreateUsers->value)->name('users.store');
    Route::get('settings/users/{user}/edit', EditUserController::class)
        ->can(Permission::UpdateUsers->value)->name('users.edit');
    Route::patch('settings/users/{user}', UpdateUserController::class)
        ->can(Permission::UpdateUsers->value)->name('users.update');
    Route::post('settings/users/{user}/password-reset-link', SendPasswordResetLinkController::class)
        ->can(Permission::UpdateUsers->value)->name('users.password-reset-link');
    Route::delete('settings/users/{user}', DeleteUserController::class)
        ->can(Permission::DeleteUsers->value)->name('users.destroy');
    Route::post('settings/users/{user}/restore', RestoreUserController::class)
        ->withTrashed()->can(Permission::DeleteUsers->value)->name('users.restore');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
