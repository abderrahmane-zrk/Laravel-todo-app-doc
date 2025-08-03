<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ إنشاء الأدوار
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // ✅ إنشاء صلاحيات
        $permissions = [
            'create tasks',
            'edit tasks',
            'delete tasks',
            'upload attachments',
            'manage users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ✅ إعطاء كل الصلاحيات لدور admin
        $admin->syncPermissions($permissions);

        // ✅ يمكنك تخصيص صلاحيات لدور user لاحقًا حسب الحاجة
        // $user->givePermissionTo('create tasks');
    }
}

