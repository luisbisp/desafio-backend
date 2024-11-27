<?php

namespace Database\Factories;

use App\Enums\IdType;
use App\Services\IdService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AnswersMetricsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
            return [
                'form_id' => IdService::create(IdType::FORM),
                'field_id' => IdService::create(IdType::FIELD),
                'views' => $this->faker->numberBetween(5, 10),
                'submits' => $this->faker->numberBetween(0, 5)
            ];
    }
}
