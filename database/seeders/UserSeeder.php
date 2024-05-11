<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@diplom.ru'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('pa$$w0rd123'),
                'active' => true,
            ]
        );
        $admin->syncRoles([$adminRole]);

        $user = User::firstOrCreate(
            ['email' => 'test@diplom.ru'],
            [
                'name' => 'Тестовый пользователь',
                'password' => Hash::make('pa$$w0rd123'),
                'active' => true,
            ]
        );
        $user->syncRoles([$userRole]);
    }
}
