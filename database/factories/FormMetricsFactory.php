<?php

namespace Database\Factories;

use App\Enums\IdType;
use App\Services\IdService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormMetrics>
 */
class FormMetricsFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition()
	{
		return [
			'form_id' => IdService::create(IdType::FORM),
            'total_respondents' => 1,
            'total_time' => 180
		];
	}
}
