<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\User;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@wasteapp.com',
            'password' => Hash::make('admin123'),
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }

        // Create demo user
        $user = User::create([
            'name' => 'User Demo',
            'username' => 'user',
            'email' => 'user@wasteapp.com',
            'password' => Hash::make('user123'),
            'phone' => '081234567891',
            'address' => 'Jl. User No. 1',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign user role
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $user->assignRole($userRole);
        }
    }
}
