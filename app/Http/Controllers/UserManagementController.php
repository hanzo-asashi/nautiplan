<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with(['unit', 'roles']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        if ($request->filled('role_id')) {
            $roleId = $request->input('role_id');
            $query->whereHas('roles', function ($q) use ($roleId) {
                $q->where('roles.id', $roleId);
            });
        }

        $users = $query->paginate(10)->withQueryString();
        $units = Unit::get(['id', 'name', 'code']);
        $roles = Role::get(['id', 'display_name']);

        return Inertia::render('users/Index', [
            'users' => $users,
            'units' => $units,
            'roles' => $roles,
            'filters' => $request->only(['search', 'unit_id', 'role_id']),
        ]);
    }

    public function create(): Response
    {
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $roles = Role::get(['id', 'display_name']);

        return Inertia::render('users/Create', [
            'units' => $units,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'employee_id' => 'nullable|string|max:50|unique:users',
            'phone' => 'nullable|string|max:20',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'required|boolean',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'],
            'phone' => $validated['phone'],
            'unit_id' => $validated['unit_id'],
            'is_active' => $validated['is_active'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->roles()->sync($validated['role_ids']);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        $user->load('roles');
        $units = Unit::where('is_active', true)->get(['id', 'name', 'code']);
        $roles = Role::get(['id', 'display_name']);

        return Inertia::render('users/Edit', [
            'user' => $user,
            'units' => $units,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'required|boolean',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_id' => $validated['employee_id'],
            'phone' => $validated['phone'],
            'unit_id' => $validated['unit_id'],
            'is_active' => $validated['is_active'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->roles()->sync($validated['role_ids']);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus diri sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
