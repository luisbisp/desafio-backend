<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\FormMetrics;

use Carbon\Carbon;

class MetricsService
{

    public function updateFormTime(Answer $answer)
    {
        $start = Carbon::parse($answer->respondent->created_at);
        $end = Carbon::parse($answer->respondent->completed_at);
 
        $timeToComplete = $start->diffInSeconds($end);

        $newMetric = FormMetrics::firstOrNew(['form_id' => $answer->form_id]);

        !$newMetric->exists && $newMetric->save();

        $newMetric->increment('total_time', $timeToComplete);
        $newMetric->increment('total_respondents');

    }
}
