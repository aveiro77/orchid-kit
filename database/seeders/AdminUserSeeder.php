<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Orchid\Platform\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'status_aktif' => true,
            ]
        );

        $adminRole = Role::where('slug', 'administrator')->first();

        if ($adminRole && ! $admin->roles()->where('slug', 'administrator')->exists()) {
            $admin->roles()->attach($adminRole);
        }
    }
}
