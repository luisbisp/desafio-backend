<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Form;
use App\Models\User;
use App\Models\Answer;
use App\Models\FormMetrics;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FormTest extends TestCase
{
	use RefreshDatabase;

	public function test_user_can_create_form()
	{
		$user = User::factory()->create();

		$formData = [
			'title' => 'Example Form ' . time(),
			'show_time_to_complete' => true,
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
				['type' => 'text', 'label' => 'Name', 'required' => true],
				['type' => 'email', 'label' => 'Email', 'required' => true]
			],
		];

		$response = $this->actingAs($user)->post('/api/forms', $formData);

		$response->assertStatus(201);
		$this->assertDatabaseHas('forms', [
			'title' => $formData['title']
		]);
	}

	public function test_user_should_send_correct_body_to_create_a_form()
	{
		$user = User::factory()->create();
		$post = $this->actingAs($user)->post('/api/forms', []);

		$post->assertJsonStructure(['errors' => ['title', 'fields']]);
		$post->assertStatus(422);
	}

	public function test_user_can_show_form()
	{
		$user = User::factory()->create();
		$form = Form::factory()->for($user)->create();

		$response = $this->actingAs($user)->get("/api/forms/$form->slug");

		$response->assertStatus(200);
		$response->assertJson($form->toArray());
	}

	public function test_show_form_with_time_to_complete()
	{
		$user = User::factory()->create();
		$form = Form::factory()->for($user)->create([
			'show_time_to_complete' => true
		]);

		$totalRespondents = 5;
		$totalTime = 1000;

		FormMetrics::factory()->for($form)->create([
			'total_respondents' => $totalRespondents,
			'total_time' => $totalTime
		]); 

		$response = $this->actingAs($user)->get("/api/forms/$form->slug");

		$response->assertStatus(200);
		$response->assertJson($form->toArray());

		$this->assertArrayHasKey('time_to_complete', $response->json());
		$this->assertNotNull($response->json()['time_to_complete']);

		//verificando se o calculo está correto
		$expectedResult = $totalTime / $totalRespondents;
		$this->assertEquals($expectedResult, $response->time_to_complete);
	}

	public function test_dont_show_form_with_time_to_complete()
	{
		$user = User::factory()->create();
		$form = Form::factory()->for($user)->create([
			'show_time_to_complete' => false
		]);
		FormMetrics::factory()->for($form)->create(); 

		$response = $this->actingAs($user)->get("/api/forms/$form->slug");
	
		$this->assertNull($response->json()['time_to_complete']);
	}

	public function test_user_can_update_form()
	{
		$user = User::factory()->create();
		$form = Form::factory()->for($user)->create(["title" => "old title"]);

		$updatedData = [
			'title' => 'Updated Title',
			'show_time_to_complete' => true,
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
					'label' => 'Name ' . time(),
					'required' => true,
					'field_id' => "field_123"
				]
			]
		];

		$put = $this->actingAs($user)->put('/api/forms/' . $form->slug, $updatedData);

		$put->assertStatus(200);
		$this->assertDatabaseHas('forms', [
			'title' => 'Updated Title',
			'show_time_to_complete' => true
		]);
	}

	/** @test */
	public function test_user_can_delete_form()
	{

		$user = User::factory()->create();
		$form = Form::factory()->for($user)->create();

		$response = $this->actingAs($user)->delete('/api/forms/' . $form->slug);

		$response->assertStatus(204);
		$this->assertDatabaseMissing('forms', ['id' => $form->slug]);
	}

	public function test_user_can_list_their_forms()
	{
		$user = User::factory()->create();
		$forms = Form::factory(5)->for($user)->create();
		$get = $this->actingAs($user)->get('/api/forms/');

		$get->assertStatus(200);
		$get->assertJsonCount(5, 'data');
	}

	public function test_user_cannot_list_other_users_forms()
	{
		$user = User::factory()->create();
		$form = Form::factory()->create(['user_id' => $user->public_id]);
		$this->assertDatabaseHas("forms", ["slug" => $form->slug]);

		// Anonymus
		$response = $this->get('/api/forms/');
		$response->assertStatus(401);

		// Other user
		$user = User::factory()->create();
		$response = $this->actingAs($user)->get('/api/forms/');
		$response->assertStatus(200);
		$response->assertJsonCount(0, 'data');
	}

	public function test_user_cannot_update_other_users_forms()
	{
		$user = User::factory()->create();
		$form = Form::factory()->create(['user_id' => $user->public_id, "title" => "test"]);
		$this->assertDatabaseHas("forms", ["slug" => $form->slug, "title" => "test"]);

		// Anonymus
		$response = $this->put("/api/forms/$form->slug");
		$response->assertStatus(401);

		// Other user
		$user = User::factory()->create();
		$response = $this->actingAs($user)->put("/api/forms/$form->slug", ["title" => time()]);
		$response->assertStatus(404);
		$this->assertDatabaseHas("forms", ["slug" => $form->slug, "title" => "test"]);
	}

	public function test_user_cannot_delete_other_users_forms()
	{
		$user = User::factory()->create();
		$form = Form::factory()->create(['user_id' => $user->public_id, "title" => "test"]);
		$this->assertDatabaseHas("forms", ["slug" => $form->slug]);

		// Anonymus
		$response = $this->delete("/api/forms/$form->slug");
		$response->assertStatus(401);

		// Other user
		$user = User::factory()->create();
		$response = $this->actingAs($user)->delete("/api/forms/$form->slug");
		$response->assertStatus(404);
		$this->assertDatabaseHas("forms", ["slug" => $form->slug, "title" => "test"]);
	}

	public function test_form_generates_field_id_for_each_field()
	{
		$fields = [
			[
				'type' => 'text',
				'label' => 'field label 1',
				'required' => false,
			],
			[
				'type' => 'text',
				'label' => 'field label 2',
				'required' => false,
			],
		];

		$user = User::factory()->create();
		$form = Form::factory()->create(['user_id' => $user->public_id, "fields" => $fields]);
		$this->assertNotNull($form->fields[0]["field_id"]);
		$this->assertNotNull($form->fields[1]["field_id"]);
	}

	public function test_create_with_respondent_email_without_email_field(){
		$user = User::factory()->create();

		$formData = [
			'title' => 'Example Form ' . time(),
			'show_time_to_complete' => true,
			'notification' => [
				'email' => false,
				'whatsapp' => false,
				'respondent_email' => true,
				'webhook' => [
					'active' => false,
					'url' => 'testwebhook',
				]
			],
			'fields' => [
				['type' => 'text', 'label' => 'Name', 'required' => true]
			],
		];

		$response = $this->actingAs($user)->post('/api/forms', $formData);

		$response->assertStatus(400);
		
	}
}
