<?php

namespace Database\Factories;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Referral>
 */
class ReferralFactory extends Factory
{
    protected $model = Referral::class;

    public function definition(): array
    {
        return [
            'pemberi_referral_id' => User::factory(),
            'penerima_referral_id' => User::factory(),
            'client_name' => fake()->name(),
            'project_name' => fake()->catchPhrase(),
            'nilai_estimasi' => fake()->randomFloat(2, 500000, 50000000),
            'status' => fake()->randomElement(['introduced', 'follow_up', 'negotiation', 'won', 'lost']),
            'catatan' => fake()->sentence(),
        ];
    }
}
