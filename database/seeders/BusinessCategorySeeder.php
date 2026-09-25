<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Kuliner',
            'Fashion Muslim',
            'Perdagangan/Retail',
            'Jasa & Konsultasi',
            'Teknologi & Digital',
            'Pendidikan & Pelatihan',
            'Kesehatan & Kecantikan',
            'Keuangan Syariah',
            'Pertanian & Perikanan',
            'Konstruksi & Properti',
            'Otomotif',
        ];

        foreach ($categories as $nama) {
            BusinessCategory::firstOrCreate(
                ['slug' => Str::slug($nama)],
                ['nama' => $nama]
            );
        }
    }
}
