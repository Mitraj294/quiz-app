<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users with roles and authored quizzes.
     */
    public function index(Request $request): View
    {
        // Load users with roles and authored quizzes to avoid N+1 queries
        $all = User::with(['roles', 'authoredQuizzes'])->orderBy('name');

        // Authors: users who have the 'author' role
        $authors = (clone $all)->whereHas('roles', function ($q) {
            $q->where('role', 'author');
        })->get();

        // Regular users: those who are not admins and not authors
        $users = (clone $all)
            ->whereDoesntHave('roles', function ($q) {
                $q->where('role', 'admin');
            })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('role', 'author');
            })
            ->get();

    $roles = Role::all();
    return view('users.index', compact('authors', 'users', 'roles'));
    }
    
    /**
     * Handle Add User form submission from index page
     */
    public function addUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:user,author',
        ]);

        // Create user with default password '123456'
        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('123456'),
        ]);

        // Assign role
        $role = \App\Models\Role::where('role', $data['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return redirect()->back()->with('status', 'User created successfully!');
    }

    /**
     * Assign a role to a user (by role name passed in request->role)
     */
    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => 'required|string']);

        $role = Role::where('role', $request->input('role'))->first();
        if (! $role) {
            return back()->withErrors(['role' => 'Role not found']);
        }

        // Prevent assigning admin via this UI
        if ($role->role === 'admin') {
            return back()->withErrors(['role' => 'Cannot assign admin role here']);
        }

        // If user currently has admin role, preserve it. Remove any other roles
        $hasAdmin = $user->roles()->where('role', 'admin')->exists();

        // Detach all non-admin roles first
        $nonAdminRoleIds = Role::where('role', '!=', 'admin')->pluck('id')->toArray();
        if (! empty($nonAdminRoleIds)) {
            $user->roles()->detach($nonAdminRoleIds);
        }

        // Attach requested role if not present
        if (! $user->roles()->where('role', $role->role)->exists()) {
            $user->roles()->attach($role->id);
        }

        // If user had admin, ensure admin remains attached (sync shouldn't remove admin)
        if ($hasAdmin && ! $user->roles()->where('role', 'admin')->exists()) {
            $adminRole = Role::where('role', 'admin')->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole->id);
            }
        }

        return back()->with('status', 'Role assigned');
    }

    /**
     * Remove a role from a user by role id or name.
     */
    public function removeRole(Request $request, User $user, $role): RedirectResponse
    {
        // $role may be id or role name; attempt to resolve
        if (is_numeric($role)) {
            $roleModel = Role::find($role);
        } else {
            $roleModel = Role::where('role', $role)->first();
        }

        if (! $roleModel) {
            return back()->withErrors(['role' => 'Role not found']);
        }

        $user->roles()->detach($roleModel->id);

        return back()->with('status', 'Role removed');
    }
}
