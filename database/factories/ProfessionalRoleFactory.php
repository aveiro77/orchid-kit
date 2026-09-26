<?php

namespace Database\Factories;

use App\Models\ProfessionalRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfessionalRole>
 */
class ProfessionalRoleFactory extends Factory
{
    protected $model = ProfessionalRole::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->jobTitle(),
        ];
    }
}
