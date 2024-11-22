<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Form;
use App\Models\Respondent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Services\MetricsService;
use Illuminate\Support\Facades\Log;

class MetricsTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function test_create_total_time_and_increment_respondent(){

        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create([
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


        $respondent = Respondent::factory()->for($form)->create([
            'created_at' => now()->subMinutes(3),
            'completed_at' => now(),   
        ]);
        $answer = Answer::factory()->for($form)->for($respondent)->make();
		$answer->is_last = true;

        $post = $this->post('/api/answers', $answer->toArray());
        
        $this->assertNotNull($post['data']['respondent']);

        $this->assertDatabaseHas('form_metrics', [
            'form_id' => $form->slug,
            'total_time' => 180,
            'total_respondents' => 1,
        ]);

        //testando se o incremento funciona
        $answer = Answer::factory()->for($form)->for($respondent)->make();
        $answer->is_last = true;

        $post = $this->post('/api/answers', $answer->toArray());

        $this->assertDatabaseHas('form_metrics', [
            'form_id' => $form->slug,
            'total_time' => 360,
            'total_respondents' => 2,
        ]);


        //testando se não incrementa se não for a ultima resposta(completa)
        $answer = Answer::factory()->for($form)->for($respondent)->make();
        $post = $this->post('/api/answers', $answer->toArray());

        $this->assertDatabaseHas('form_metrics', [
            'form_id' => $form->slug,
            'total_time' => 360,
            'total_respondents' => 2,
        ]);
    }

}
