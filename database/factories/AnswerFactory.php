<?php

namespace Database\Factories;

use App\Enums\IdType;
use App\Services\IdService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition()
	{
		return [
			'respondent_id' => IdService::create(IdType::RESPONDENT),
			'form_id' => '__' . IdService::create(IdType::FORM),
			'question' => $this->faker->sentence,
			'value' => $this->faker->word,
			'field_id' => IdService::create(IdType::FIELD),
			'type' => $this->faker->randomElement(['text', 'radio', 'url']),
		];
	}
}
