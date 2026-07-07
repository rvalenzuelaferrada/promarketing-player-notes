<?php

namespace Database\Factories;

use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerNote>
 */
class PlayerNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'player_id' => User::factory(),
            'author_id' => User::factory(),
            'content' => fake()->sentence(),
        ];
    }
}
