<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Waste Report permissions
            'view waste reports',
            'create waste reports',
            'edit waste reports',
            'delete waste reports',
            'approve waste reports',
            'reject waste reports',

            // User management permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',

            // Waste type permissions
            'view waste types',
            'create waste types',
            'edit waste types',
            'delete waste types',

            // Dashboard permissions
            'view dashboard',
            'view admin dashboard',
            'view user dashboard',

            // Map permissions
            'view map',
            'add location',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'view waste reports',
            'create waste reports',
            'edit waste reports',
            'view waste types',
            'view dashboard',
            'view user dashboard',
            'view map',
            'add location',
        ]);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $petugasRole = Role::create(['name' => 'petugas']);
        $petugasRole->givePermissionTo([
            'view waste reports',
            'create waste reports',
            'edit waste reports',
            'view waste types',
            'view dashboard',
            'view user dashboard',
            'view map',
            'add location',
        ]);
    }
}
