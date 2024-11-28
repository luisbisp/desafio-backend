<?php

namespace App\Services;

use App\Models\AnswersMetrics;
use App\Models\FormMetrics;
use Carbon\Carbon;

class MetricsService
{

    public function updateFormTime($respondent, $form_id)
    {
        $start = Carbon::parse($respondent->created_at);
        $end = Carbon::parse($respondent->completed_at);

        $timeToComplete = $start->diffInSeconds($end);

        $newMetric = FormMetrics::firstOrNew(['form_id' => $form_id]);

        !$newMetric->exists && $newMetric->save();

        $newMetric->increment('total_time', $timeToComplete);
        $newMetric->increment('total_respondents');
    }


    public function updateAnswerMetrics($answer)
    {
        $form_id = $answer['form_id'];
        $field_id = $answer['field_id'];

        $newMetric = AnswersMetrics::firstOrNew([
            'form_id' => $form_id,
            'field_id' => $field_id,
        ]);

        !$newMetric->exists && $newMetric->save();

        match ($answer['type']) {
            'submit' =>  $newMetric->increment('submits'),
            'view' => $newMetric->increment('views'),
        };

    }

}
