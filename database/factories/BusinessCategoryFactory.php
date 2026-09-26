<?php

namespace Database\Factories;

use App\Models\BusinessCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BusinessCategory>
 */
class BusinessCategoryFactory extends Factory
{
    protected $model = BusinessCategory::class;

    public function definition(): array
    {
        $nama = fake()->unique()->companySuffix() . ' ' . fake()->word();
        return [
            'nama' => ucfirst($nama),
            'slug' => Str::slug($nama),
        ];
    }
}
