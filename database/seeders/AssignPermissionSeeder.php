<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(3); // أو أي ID

        if ($user && !$user->hasPermissionTo('delete tasks')) {
            $user->givePermissionTo('delete tasks');
            $this->command->info("تمت إضافة صلاحية 'delete tasks' لليوزر رقم {$user->id}.");
        } else {
            $this->command->info("المستخدم غير موجود أو يملك الصلاحية مسبقاً.");
        }
    }
}
