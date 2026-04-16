<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('sends password reset link email', function () {
    Notification::fake();

    Role::create([
        'id' => 1,
        'role_name' => Role::USER,
        'description' => 'Default role',
        'level' => 1,
    ]);

    $user = User::factory()->create([
        'email' => 'reset@example.com',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => $user->email,
    ])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets password with valid token', function () {
    Notification::fake();

    Role::create([
        'id' => 1,
        'role_name' => Role::USER,
        'description' => 'Default role',
        'level' => 1,
    ]);

    $user = User::factory()->create([
        'email' => 'reset2@example.com',
    ]);

    $this->postJson('/api/auth/forgot-password', [
        'email' => $user->email,
    ])->assertOk();

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->postJson('/api/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ])->assertOk();

    expect(Hash::check('NewPassword1!', $user->fresh()->password))->toBeTrue();
});
