<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'Web Development',
            'Graphic Design',
            'Accounting',
            'Digital Marketing',
            'Photography',
            'Legal Consultant',
        ];

        foreach ($skills as $nama) {
            Skill::firstOrCreate(['nama' => $nama]);
        }
    }
}
