<?php

namespace Tests\Feature;

use App\Models\Answer;
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
    public function test_update_submit_metrics()
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
}
