<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use App\Models\Answer;
use App\Models\Respondent;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserDataSeeder extends Seeder
{

	private $totalUsers = 1;
	private $totalForms = 1;
	private $totalRespondents = 1;
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{

		User::factory()->count($this->totalUsers)->create()->each(function ($user) {

			// Create forms
			Form::factory($this->totalForms)->create(['user_id' => $user->public_id])->each(function ($form) {


				// Create respondents
				Respondent::factory()->count($this->totalRespondents)->create(['form_id' => $form->slug])->each(function ($respondent) use ($form) {
					$fields = $form->fields;

					foreach ($fields as $index => $field) {

						Answer::factory()->create([
							'field_id' => $field["field_id"],
							'question' => $field["label"],
							'respondent_id' => $respondent->public_id,
							'form_id' => $form->slug,
						]);
					}
				});
			});
		});
	}
}
