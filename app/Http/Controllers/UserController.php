<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserResource::collection(User::with('role')->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user->load('role'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'display_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'sometimes|nullable|exists:roles,id',
        ]);

        $validated['password'] = Str::random(32);

        $user = User::create($validated);

        return (new UserResource($user->load('role')))->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'display_name' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role_id' => 'sometimes|nullable|exists:roles,id',
        ]);

        $user->update($validated);

        return new UserResource($user->load('role'));
    }

    /**
     * Upload and save the user's profile icon.
     */
    public function uploadProfileIcon(Request $request, User $user)
    {
        $validated = $request->validate([
            'profile_icon' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        if ($user->profile_icon && Storage::disk('public')->exists($user->profile_icon)) {
            Storage::disk('public')->delete($user->profile_icon);
        }

        $path = $validated['profile_icon']->store('profile-icons', 'public');

        $user->update([
            'profile_icon' => $path,
        ]);

        return new UserResource($user->fresh()->load('role'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }
}
