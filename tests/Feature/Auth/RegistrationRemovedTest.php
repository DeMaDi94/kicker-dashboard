<?php

declare(strict_types=1);

use App\Models\User;

describe('B14 · no self-registration', function () {
    it('has no registration screen and accepts no sign-up', function () {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Someone',
            'email' => 'someone@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        expect(User::count())->toBe(0);
    });
});
