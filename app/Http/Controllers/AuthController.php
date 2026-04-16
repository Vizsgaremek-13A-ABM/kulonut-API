<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @unauthenticated
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $defaultRoleId = Role::roleIdFor(Role::USER);

        if ($defaultRoleId === null) {
            throw ValidationException::withMessages([
                'role' => ['Default user role is not configured.'],
            ]);
        }

        $validated['role_id'] = $defaultRoleId;
        $user = User::create($validated);
        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('role')),
        ], 201);
    }

    /**
     * Login a user and create a token.
     *
     * @unauthenticated
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'message' => 'Login successful.',
            'token' => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('role')),
        ]);
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        } else {
            $request->user()->tokens()->delete();
        }

        return response()->json(['message' => 'Logout successful.']);
    }

    /**
     * Get the authenticated user's information.
     */
    public function me(Request $request)
    {
        return new UserResource($request->user()->load('role'));
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        if (Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The new password cannot be the same as your current password.'],
            ]);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Password updated successfully.'], 201);
    }

    /**
     * Send a password reset link to the user.
     *
     * @unauthenticated
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'If your email exists in our system, you will receive a password reset link shortly.',
        ]);
    }

    /**
     * Reset the user's password.
     *
     * @unauthenticated
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    /**
     * Verify the user's email address.
     *
     * @unauthenticated
     */
    public function verifyEmail(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully.',
        ]);
    }

    /**
     * Resend email verification notification.
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent.',
        ]);
    }
}
