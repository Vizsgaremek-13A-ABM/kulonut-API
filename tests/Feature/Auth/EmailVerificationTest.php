<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('sends verification email on registration', function () {
    Notification::fake();

    Role::create([
        'role_name' => Role::USER,
        'description' => 'Default role',
        'level' => 1,
    ]);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'test@example.com')->firstOrFail();
    expect($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('verifies user email with signed link', function () {
    Role::create([
        'id' => 1,
        'role_name' => Role::USER,
        'description' => 'Default role',
        'level' => 1,
    ]);

    $user = User::factory()->unverified()->create([
        'email' => 'verify@example.com',
    ]);

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->getJson($url)
        ->assertOk()
        ->assertJson([
            'message' => 'Email verified successfully.',
        ]);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('resends verification email for authenticated unverified users', function () {
    Notification::fake();

    Role::create([
        'id' => 1,
        'role_name' => Role::USER,
        'description' => 'Default role',
        'level' => 1,
    ]);

    $user = User::factory()->unverified()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/auth/email/verification-notification')
        ->assertOk()
        ->assertJson([
            'message' => 'Verification link sent.',
        ]);

    Notification::assertSentTo($user, VerifyEmail::class);
});
