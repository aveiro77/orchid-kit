<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 month', '+2 months');
        $endDate = (clone $startDate)->modify('+3 hours');

        return [
            'judul' => 'Kajian & Networking: ' . fake()->catchPhrase(),
            'deskripsi' => fake()->paragraphs(2, true),
            'lokasi' => 'Hotel Santika Pekalongan',
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'kuota' => fake()->numberBetween(20, 100),
            'status' => 'published',
        ];
    }
}
