<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\AnswersMetrics;
use App\Models\Form;
use App\Models\Respondent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnswersMetricsTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function test_update_submit_answer_metrics()
    {

        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();
        $respondent = Respondent::factory()->for($form)->create();
        $answer = Answer::factory()->for($form)->for($respondent)->create();

        $this->post('/api/answers', $answer->toArray());

        $numViews = 0;
        $numSubmits = 1;

        $this->assertDatabaseHas('answers_metrics', [
            'form_id' => $form->slug,
            'field_id' => $answer->field_id,
            'views' => $numViews,
            'submits' => $numSubmits
        ]);
    }

    /** @test */
    public function test_update_view_answer_metrics()
    {
        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();
        $fieldId = $form->fields[0]['field_id'];

        $post = $this->post('/api/answers/metrics', [
            'form_id' => $form->slug,
            'field_id' => $fieldId,
            'type' => 'view'
        ]);

        $post->assertStatus(200);


        $numViews = 1;
        $numSubmits = 0;
    
        $this->assertDatabaseHas('answers_metrics', [
            'form_id' => $form->slug,
            'field_id' => $fieldId,
            'views' => $numViews,
            'submits' => $numSubmits
        ]);
    }

    /** @test */
    public function test_cant_update_invalid_answer_metrics()
    {
        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();
        $respondent = Respondent::factory()->for($form)->create();
        $answer = Answer::factory()->for($form)->for($respondent)->create();

        $post = $this->post('/api/answers/metrics', [
            'form_id' => $form->slug,
            'field_id' => $answer->field_id,
            'type' => 'invalid'
        ]);

        $post->assertStatus(422);
        $this->assertDatabaseMissing('answers_metrics', [
            'form_id' => $form->slug,
            'field_id' => $answer->field_id
        ]);
    }

    /** @test */
    public function test_cant_update_with_invalid_form_id_answer_metrics()
    {
        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();
        $respondent = Respondent::factory()->for($form)->create();
        $answer = Answer::factory()->for($form)->for($respondent)->create();

        $post = $this->post('/api/answers/metrics', [
            'form_id' => 'invalid',
            'field_id' => $answer->field_id,
            'type' => 'view'
        ]);

        $post->assertStatus(422);
        $this->assertDatabaseMissing('answers_metrics', [
            'form_id' => $form->slug,
            'field_id' => $answer->field_id
        ]);
    }

    /** @test */
    public function test_cant_update_with_invalid_field_id_answer_metrics()
    {
        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();
        $respondent = Respondent::factory()->for($form)->create();
        $answer = Answer::factory()->for($form)->for($respondent)->create();

        $post = $this->post('/api/answers/metrics', [
            'form_id' => $form->slug,
            'field_id' => 'invalid',
            'type' => 'view'
        ]);

        $post->assertStatus(422);
        $this->assertDatabaseMissing('answers_metrics', [
            'form_id' => $form->slug,
            'field_id' => $answer->field_id
        ]);
    }

    /** @test */
    public function test_can_show_answers_metrics(){

        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();

        foreach ($form->fields as $field) {
            AnswersMetrics::factory()->for($form)->create([
                'form_id' => $form->slug,
                'field_id' => $field['field_id'],
            ]);
        }
        
        $response = $this->actingAs($user)->get("/api/forms/$form->slug/metrics");
        $response->assertStatus(200);
    }

    public function test_cant_show_answers_metrics_for_another_user(){

        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();
        $response = $this->get("/api/forms/$form->slug/metrics");
        $response->assertStatus(401);

    }
}
