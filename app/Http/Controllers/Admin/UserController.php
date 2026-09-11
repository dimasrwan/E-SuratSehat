<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'operator'])],
            'is_active' => 'sometimes|boolean',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->is_active = $request->has('is_active') ? $request->boolean('is_active') : true;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'operator'])],
            'is_active' => 'sometimes|boolean',
        ]);

        $newIsActive = $request->has('is_active') ? $request->boolean('is_active') : $user->is_active;
        $newRole = $validated['role'];

        // Guard 1: Cannot self-deactivate or self-demote
        if ($user->id === Auth::id()) {
            if (!$newIsActive) {
                return back()->withErrors(['is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'])->withInput();
            }
            if ($newRole !== 'admin') {
                return back()->withErrors(['role' => 'Anda tidak dapat mengubah role Anda sendiri menjadi operator.'])->withInput();
            }
        }

        // Guard 2: Ensure at least one active admin remains in system
        if ($user->role === 'admin' && $user->is_active) {
            if ($newRole !== 'admin' || !$newIsActive) {
                $otherActiveAdmins = User::where('role', 'admin')
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($otherActiveAdmins === 0) {
                    return back()->withErrors(['role' => 'Sistem harus memiliki minimal satu administrator yang aktif.'])->withInput();
                }
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->role = $newRole;
        $user->is_active = $newIsActive;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Toggle status (activate/deactivate) of the user.
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($user->is_active && $user->role === 'admin') {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Sistem harus memiliki minimal satu administrator yang aktif.');
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusMessage = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.users.index')
            ->with('success', "Akun pengguna {$user->name} berhasil {$statusMessage}.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'admin' && $user->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                return back()->with('error', 'Sistem harus memiliki minimal satu administrator yang aktif.');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
