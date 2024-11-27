<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Form;
use App\Models\Respondent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


use Illuminate\Support\Facades\Log;

class FormMetricsTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function test_create_total_time_and_increment_respondent()
    {

        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();

        $respondent = Respondent::factory()->for($form)->create([
            'created_at' => now()->subMinutes(3),
            'completed_at' => now(),
        ]);

        $answer = Answer::factory()->for($form)->for($respondent)->make();
        $answer->is_last = true;

        $this->post('/api/answers', $answer->toArray());

        $totalTime = 180;
        $totalRespondents = 1;
        $this->assertDatabaseHas('form_metrics', [
            'form_id' => $form->slug,
            'total_time' => $totalTime,
            'total_respondents' => $totalRespondents,
        ]);

        //testando se o incremento funciona
        $answer = Answer::factory()->for($form)->for($respondent)->make();
        $answer->is_last = true;

        $this->post('/api/answers', $answer->toArray());

        $totalTime = 360;
        $totalRespondents = 2;
        $this->assertDatabaseHas('form_metrics', [
            'form_id' => $form->slug,
            'total_time' => $totalTime,
            'total_respondents' => $totalRespondents,
        ]);
    }

    public function test_dont_increment_total_time_and_increment_respondent_if_not_last_answer()
    {

        $user = User::factory()->create();
        $form = Form::factory()->for($user)->create();

        $respondent = Respondent::factory()->for($form)->create();

        $answer = Answer::factory()->for($form)->for($respondent)->make();

        $this->post('/api/answers', $answer->toArray());

        $this->assertDatabaseMissing('form_metrics', [
            'form_id' => $form->slug,
        ]);

    }
}
