<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'text' => fake()->sentence(8),
            'topic' => fake()->randomElement(Quote::TOPICS),
            'source_title' => null,
            'source_author' => null,
        ];
    }

    /**
     * A quote that says where it comes from.
     */
    public function withSource(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_title' => fake()->sentence(3),
            'source_author' => fake()->name(),
        ]);
    }
}
