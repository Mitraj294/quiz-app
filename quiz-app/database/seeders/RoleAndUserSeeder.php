<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['role' => 'admin']);
        $userRole = Role::firstOrCreate(['role' => 'user']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Attach admin role
        if (!$admin->roles()->where('role', 'admin')->exists()) {
            $admin->roles()->attach($adminRole);
        }

        $this->command->info('✅ Admin user created: admin@example.com / password');

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Attach user role
        if (!$user->roles()->where('role', 'user')->exists()) {
            $user->roles()->attach($userRole);
        }

        $this->command->info('✅ Regular user created: user@example.com / password');
    }
}
