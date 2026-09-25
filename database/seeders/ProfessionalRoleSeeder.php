<?php

namespace Database\Seeders;

use App\Models\ProfessionalRole;
use Illuminate\Database\Seeder;

class ProfessionalRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Pengusaha',
            'Freelancer',
            'Konsultan',
            'Mentor',
            'Investor',
            'Reseller',
            'Programmer',
            'Designer',
            'Marketing',
        ];

        foreach ($roles as $nama) {
            ProfessionalRole::firstOrCreate(['nama' => $nama]);
        }
    }
}
