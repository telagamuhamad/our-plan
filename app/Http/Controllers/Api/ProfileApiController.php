<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileApiController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show()
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'timezone' => 'nullable|timezone',
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, dan underscore.',
        ]);

        try {
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'timezone' => $request->timezone ?? $user->timezone,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui!',
                'data' => $user->fresh()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.',
                'errors' => [
                    'current_password' => ['Password saat ini salah.']
                ]
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the user's profile picture (avatar).
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = Auth::user();

        try {
            if ($request->hasFile('avatar')) {
                // Delete old avatar if it's stored locally
                if ($user->avatar_url && !str_starts_with($user->avatar_url, 'http')) {
                    $oldPath = str_replace('/storage/', '', $user->avatar_url);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar_url = '/storage/' . $path;
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui!',
                'data' => [
                    'avatar_url' => $user->avatar_url
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the user's profile picture.
     */
    public function removeAvatar()
    {
        $user = Auth::user();

        try {
            if ($user->avatar_url && !str_starts_with($user->avatar_url, 'http')) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $user->avatar_url = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil dihapus!',
                'data' => [
                    'avatar_url' => null
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
