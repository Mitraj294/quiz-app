<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure admin and user roles exist with explicit ids if possible
    $roleAdmin = Role::firstOrCreate(['role' => 'admin']);
    $roleUser = Role::firstOrCreate(['role' => 'user']);
    // Ensure author role exists
    Role::firstOrCreate(['role' => 'author']);

        $user = User::firstOrCreate([
            'email' => 'sdolphoin632@gmail.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('superadmin@123'),
        ]);

        if (! $user->roles()->where('role', 'admin')->exists()) {
            $user->roles()->attach($roleAdmin->id);
        }
        if (! $user->roles()->where('role', 'user')->exists()) {
            $user->roles()->attach($roleUser->id);
        }
        if (! $user->roles()->where('role', 'author')->exists()) {
            // don't auto-attach author to super admin by default, leave commented
            // $user->roles()->attach($roleAuthor->id);
        }
        
    }
}
