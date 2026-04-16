<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * List all users (admin only)
     */
    public function index(Request $request)
    {
        $query = User::withCount('bookings');

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json($users, 200);
    }

    /**
     * Show a single user (admin only)
     */
    public function show(string $id)
    {
        $user = User::withCount('bookings')
            ->with([
                'bookings' => fn($q) => $q->latest()->limit(5),
                'bookings.showtime.movie:id,title,poster',
                'bookings.payment',
            ])->findOrFail($id);

        return response()->json([
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'phone'          => $user->phone,
            'date_of_birth'  => $user->date_of_birth,
            'avatar'         => $user->avatar
                ? Storage::url($user->avatar)
                : null,
            'role'           => $user->role,
            'bookings_count' => $user->bookings_count,
            'recent_bookings'=> $user->bookings,
            'created_at'     => $user->created_at,
        ], 200);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        $user = $request->user()->loadCount('bookings');

        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'date_of_birth' => $user->date_of_birth,
            'avatar'        => $user->avatar
                ? Storage::url($user->avatar)
                : null,
            'role'          => $user->role,
            'bookings_count'=> $user->bookings_count,
            'created_at'    => $user->created_at,
        ], 200);
    }

    /**
     * Update authenticated user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'avatar'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data'    => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'avatar'        => $user->avatar
                    ? Storage::url($user->avatar)
                    : null,
            ],
        ], 200);
    }

    /**
     * Update email — requires password confirmation
     */
    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'required|string',
        ]);

        // Verify current password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password is incorrect.',
            ], 422);
        }

        $user->update(['email' => $request->email]);

        return response()->json([
            'message' => 'Email updated successfully.',
            'email'   => $user->email,
        ], 200);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
            ],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all tokens — force re-login on all devices
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password changed successfully. Please login again.',
        ], 200);
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if (!$user->avatar) {
            return response()->json([
                'message' => 'No avatar to delete.',
            ], 422);
        }

        Storage::disk('public')->delete($user->avatar);
        $user->update(['avatar' => null]);

        return response()->json([
            'message' => 'Avatar deleted successfully.',
        ], 200);
    }

    /**
     * Get user booking history
     */
    public function bookings(Request $request)
    {
        $user = $request->user();

        $query = $user->bookings()
            ->with([
                'showtime.movie:id,title,slug,poster',
                'showtime.hall.cinema:id,name',
                'bookingSeats.seat',
                'payment',
            ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Upcoming or past filter
        if ($request->has('filter')) {
            $today = now()->toDateString();
            if ($request->filter === 'upcoming') {
                $query->whereHas('showtime', fn($q) => $q->where('show_date', '>=', $today));
            } elseif ($request->filter === 'past') {
                $query->whereHas('showtime', fn($q) => $q->where('show_date', '<', $today));
            }
        }

        $bookings = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json($bookings, 200);
    }

    /**
     * Update user role (admin only)
     */
    public function updateRole(Request $request, string $id)
    {
        $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        // Prevent changing own role
        if ($request->user()->id == $id) {
            return response()->json([
                'message' => 'You cannot change your own role.',
            ], 422);
        }

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return response()->json([
            'message' => "User role updated to {$request->role}.",
            'data'    => $user->only(['id', 'name', 'email', 'role']),
        ], 200);
    }

    /**
     * Delete a user (admin only)
     */
    public function destroy(Request $request, string $id)
    {
        // Prevent deleting yourself
        if ($request->user()->id == $id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user = User::findOrFail($id);

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Revoke all tokens
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }

    /**
     * Get user stats (admin only)
     */
    public function stats()
    {
        $totalUsers    = User::where('role', 'user')->count();
        $totalAdmins   = User::where('role', 'admin')->count();
        $newThisMonth  = User::where('role', 'user')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $topCustomers  = User::where('role', 'user')
            ->withCount('bookings')
            ->withSum('bookings', 'total_amount')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'email', 'bookings_count', 'bookings_sum_total_amount']);

        return response()->json([
            'total_users'   => $totalUsers,
            'total_admins'  => $totalAdmins,
            'new_this_month'=> $newThisMonth,
            'top_customers' => $topCustomers,
        ], 200);
    }
}