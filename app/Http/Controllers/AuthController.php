<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
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
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\.]).{8,}$/|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
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
}
