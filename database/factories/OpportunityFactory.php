<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tipe' => fake()->randomElement(['need', 'offer', 'collaborate']),
            'judul' => fake()->sentence(4),
            'deskripsi' => fake()->paragraphs(2, true),
            'lokasi' => fake()->randomElement(['Pekalongan', 'Batang', 'Pemalang', 'Online / Remote']),
            'tanggal_expired' => fake()->dateTimeBetween('+1 week', '+2 months'),
            'status' => 'published',
        ];
    }
}
