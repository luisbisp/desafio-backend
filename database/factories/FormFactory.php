<?php

namespace Database\Factories;

use App\Enums\IdType;
use App\Services\IdService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition()
	{
		return [
			'slug' => IdService::create(IdType::FORM),
			// 'user_id' => IdService::create(IdType::USER),
			'title' => $this->faker->sentence,
			// 'show_time_to_complete' => false,
			'notification' => [
				'email' => false,
				'whatsapp' => false,
				'respondent_email' => false,
				'webhook' => [
					'active' => false,
					'url' => 'testwebhook',
				]
			],
			'fields' => [
				[
					'type' => 'text',
					'label' => 'Name',
					'required' => true,
					'field_id' => IdService::create(IdType::FIELD),
				],
				[
					'type' => 'email',
					'label' => 'Email',
					'required' => true,
					'field_id' => IdService::create(IdType::FIELD),
				]
			],
		];
	}
}
