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
        
    }
}
