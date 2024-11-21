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

class Metrics extends TestCase
{

    use RefreshDatabase;

    public function test_create_total_time(){

        $user = User::factory()->create();
        $form = Form::factory()->create([
            'created_at' => "2024-11-19T20:25:36.000000Z",
            'completed_at' => "2024-11-19T20:28:36.000000Z",
        ]);

        $respondent = Respondent::factory()->for($form)->create();
        $answer = Answer::factory()->for($form)->for($respondent)->make();
		$answer->is_last = true;

        $post = $this->post('/api/answers', $answer->toArray());
        
        $this->assertNotNull($post['data']['respondent']);

        $metricService = new MetricsService();
        $newMetric = $metricService->updateFormTime($answer);

        $this->assertNotNull($newMetric);
        $this->assertEquals(2, $newMetric->respondents);
        $this->assertEquals(180, $newMetric->timeToComplete);

        //testar que form tem tempo atualizado eque 
        // $timestamp = Carbon::parse($iso8601Date)->timestamp;
    }

}
