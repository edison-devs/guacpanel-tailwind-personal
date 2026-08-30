<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomerPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear el permiso
        $permission = Permission::firstOrCreate(['name' => 'manage-customers']);

        // Asignarlo al rol superuser si existe
        $role = Role::where('name', 'superuser')->first();
        if ($role) {
            $role->givePermissionTo($permission);
        }
    }
}
