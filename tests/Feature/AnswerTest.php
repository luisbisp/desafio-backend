<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Form;
use App\Models\Answer;
use App\Models\Respondent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnswerTest extends TestCase
{

	use RefreshDatabase;

	/** @test */
	public function test_respondent_can_store_answers()
	{
		$form = Form::factory()->create();
		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make();
		$post = $this->post('/api/answers', $answer->toArray());

		$post->assertStatus(201);
		$post->assertJsonPath('data.question', $answer->question);
	}

	/** @test */
	public function test_respondent_can_complete_a_session()
	{
		$form = Form::factory()->create([
			'notification' => [
				'email' => false,
				'whatsapp' => false,
				'respondent_email' => false,
				'webhook' => [
					'active' => false,
					'url' => null,
				]
			]
		]);

		$user = User::factory()->create();
		$form->user_id = $user->public_id;


		$answer = Answer::factory()->for($form)->make(["respondent_id" => null]);
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		dd([$user, $form]);

		$this->assertDatabaseHas("respondents", [
			"public_id" => $post['data']['respondent'],
		]);

		$this->assertNotNull($post['data']['respondent']);
	}
}
