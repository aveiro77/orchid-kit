<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_category_id' => BusinessCategory::factory(),
            'nama_usaha' => fake()->company(),
            'deskripsi' => fake()->paragraph(),
            'alamat' => fake()->address(),
            'website' => 'https://'.fake()->domainName(),
            'status' => 'aktif',
        ];
    }
}
