<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Orchid\Platform\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'slug' => 'administrator',
                'name' => 'Administrator',
                'permissions' => [
                    'platform.index' => 1,
                    'platform.systems.roles' => 1,
                    'platform.systems.users' => 1,
                    'platform.systems.attachment' => 1,
                ],
            ],
            [
                'slug' => 'pengurus',
                'name' => 'Pengurus',
                'permissions' => [
                    'platform.index' => 1,
                    'platform.systems.users' => 1,
                ],
            ],
            [
                'slug' => 'anggota',
                'name' => 'Anggota',
                'permissions' => [
                    'platform.index' => 1,
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'permissions' => $roleData['permissions'],
                ]
            );
        }
    }
}
