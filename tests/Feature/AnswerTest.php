<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Form;
use App\Models\Answer;
use App\Models\Respondent;
use App\Models\User;
use App\Notifications\NotificationUser;
use App\Services\FormNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class AnswerTest extends TestCase
{

	use RefreshDatabase;

	/** @test */
	public function test_respondent_can_store_answers()
	{
		$user = User::factory()->create();
		$form = Form::factory()->create(['user_id' => $user->public_id]);
		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make();
		$post = $this->post('/api/answers', $answer->toArray());

		$post->assertStatus(201);
		$post->assertJsonPath('data.question', $answer->question);
	}

	/** @test */
	public function test_respondent_can_complete_a_session()
	{
		$user = User::factory()->create();
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
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


		$answer = Answer::factory()->for($form)->make(["respondent_id" => null]);
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertDatabaseHas("respondents", [
			"public_id" => $post['data']['respondent'],
		]);

		$this->assertNotNull($post['data']['respondent']);
	}

	/** @test */
	public function test_form_completed_send_email_notification_to_form_creator()
	{
		Notification::fake();
		$user = User::factory()->create();
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
			'notification' => [
				'email' => true,
				'whatsapp' => false,
				'respondent_email' => false,
				'webhook' => [
					'active' => false,
					'url' => null,
				]
			]
		]);

		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make();
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertNotNull($post['data']['respondent']);

		Notification::assertSentTo(
			$user,
			NotificationUser::class,
			function ($notification) use ($form, $user) {
				return $notification->toMail($user)->subject === "Novo preenchimento no '{$form->title}'";
			}
		);
	}

	/** @test */
	public function test_form_completed_send_whatsapp_notification_to_form_creator()
	{
		Http::fake();
		$user = User::factory()->create([
			"phone" => "11999999999"
		]);
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
			'notification' => [
				'email' => false,
				'whatsapp' => true,
				'respondent_email' => false,
				'webhook' => [
					'active' => false,
					'url' => null,
				]
			]
		]);

		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make();
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertNotNull($post['data']['respondent']);

		$service = new FormNotificationService();
		$service->notifyFormCreatorWhatsapp($form, $respondent);

		Http::assertSent(function ($request) use ($form) {
			return $request->data()['phone'] === $form->user->phone;
		});
	}


	/** @test */
	public function test_form_completed_send_whatsapp_limit_message_to_form_creator()
	{

		Notification::fake();
		$user = User::factory()->create([
			"whatsapp_msg_received_count" => 10,
			"phone" => "11999999999"
		]);
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
			'notification' => [
				'email' => false,
				'whatsapp' => true,
				'respondent_email' => false,
				'webhook' => [
					'active' => false,
					'url' => null,
				]
			]
		]);

		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make();
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertNotNull($post['data']['respondent']);

		Notification::assertSentTo(
			$user,
			NotificationUser::class,
			function ($notification) use ($form, $user) {
				return $notification->toMail($user)->subject === "Limite de mensagens WhatsApp atingido";
			}
		);
	}

	/** @test */
	public function test_form_completed_send_email_notification_to_respondent()
	{
		Notification::fake();
		$user = User::factory()->create();
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
			'notification' => [
				'email' => false,
				'whatsapp' => false,
				'respondent_email' => true,
				'webhook' => [
					'active' => false,
					'url' => null,
				]
			]
		]);

		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make([
			'value' => 'test@example.com',
			'type' => 'email',
		]);
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertNotNull($post['data']['respondent']);


		Notification::assertSentTo(
			new AnonymousNotifiable(),
			NotificationUser::class,
			function ($notification) use ($form, $user) {
				return $notification->toMail($user)->subject === "Seu preenchimento do formulário '{$form->title}' foi completado";
			}
		);
	}

	/** @test */
	public function test_form_completed_send_email_notification_to_respondent_with_invalid_email()
	{
		Notification::fake();
		$user = User::factory()->create();
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
			'notification' => [
				'email' => false,
				'whatsapp' => false,
				'respondent_email' => true,
				'webhook' => [
					'active' => false,
					'url' => null,
				]
			]
		]);

		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make([
			'value' => 'invalid-email',
			'type' => 'email',
		]);
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertNotNull($post['data']['respondent']);

		$notificationService = new FormNotificationService();
		$notificationService->notifyFormRespondentEmail($form, $respondent);
	
		Notification::assertNotSentTo(
			new AnonymousNotifiable,
			NotificationUser::class
		);
	}

	/** @test */
	public function test_form_completed_send_webhook()
	{
		Http::fake();
		$user = User::factory()->create();
		$form = Form::factory()->create([
			'user_id' => $user->public_id,
			'notification' => [
				'email' => false,
				'whatsapp' => false,
				'respondent_email' => false,
				'webhook' => [
					'active' => true,
					'url' => 'https://destinywebhook/test/api',
				]
			]
		]);

		$respondent = Respondent::factory()->for($form)->create();
		$answer = Answer::factory()->for($form)->for($respondent)->make();
		$answer->is_last = true;

		$post = $this->post('/api/answers', $answer->toArray());

		$this->assertNotNull($post['data']['respondent']);

		$service = new FormNotificationService();
		$service->notifyFormCreatorWebhook($form, $respondent);

		Http::assertSent(function ($request) use ($form) {
			return $request->data()['form'] === $form;
		});
	}
}
