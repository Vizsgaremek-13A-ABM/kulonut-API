<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Verified;
use Propaganistas\LaravelDisposableEmail\Validation\Indisposable;

class AuthController extends Controller
{
    /**
     * Register a new user.
     * @unauthenticated
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email|indisposable',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $validated['email'] = strtolower(trim($validated['email']));

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
     * @unauthenticated
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $email]);

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'message'    => 'Login successful.',
            'token'      => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user'       => new UserResource($user->load('role')),
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
     * Send a verification email to the user.
     */
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.']);
    }

    /**
     * Mark the user's email address as verified.
     */
    public function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        $redirectUrl = env('FRONTEND_URL') . '/email-verified';
        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return redirect($redirectUrl . '?status=error&message=' . urlencode('Invalid verification link.'));
        }

        if ($user->hasVerifiedEmail()) {
            return redirect($redirectUrl . '?status=error&message=' . urlencode('Email already verified.'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect($redirectUrl . '?status=success&message=' . urlencode('Email verified successfully.'));
    }

    /**
     * Handle a forgot password request.
     * @unauthenticated
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        return response()->json([
            'message' => 'If the account exists, a password reset link has been sent.',
        ]);
    }

    /**
     * Handle a reset password request.
     * @unauthenticated
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $validated['email'] = strtolower(trim($validated['email']));

        $status = Password::reset($validated, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        });

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => $status]);
        }

        throw ValidationException::withMessages([
            'email' => [$status],
        ]);
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        return response()->json(['message' => 'Password updated successfully.']);
    }
}
