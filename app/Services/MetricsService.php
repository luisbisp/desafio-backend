<?php

namespace App\Services;

use App\Jobs\UpdateViewAnswerMetrics;
use App\Models\AnswersMetrics;
use App\Models\FormMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Enums\AnswerMetricsType;

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



    
    public function updateAnswerMetrics(AnswerMetricsType $type, $answer)
    {
        $newMetric = AnswersMetrics::firstOrNew([
            'form_id' => $answer['form_id'],
            'field_id' => $answer['field_id'],
        ]);

        !$newMetric->exists && $newMetric->save();

        match ($type) {
            AnswerMetricsType::SUBMIT =>  $newMetric->increment('submits'),
            AnswerMetricsType::VIEW => $newMetric->increment('views', $answer['increments']),
        };
    }

    public function clearDeletedFields($oldFields, $updatedFields)
    {
        $oldIds = array_column($oldFields, 'field_id');
        $newIds = array_column($updatedFields, 'field_id');

        $removedIds = array_diff($oldIds, $newIds);

        AnswersMetrics::whereIn('field_id', $removedIds)->delete();
    }

    public function createAnswerMetricsJob($data)
    {
        $requestKey = 'metrics:' . $data['form_id'] . ':' . $data['field_id'];
        $existingData = Redis::get($requestKey);

        if ($existingData) {
            Redis::incr($requestKey);
            return;
        }

        $expirationTime = 300;
        Redis::setex($requestKey, $expirationTime, 1);

        $jobExecutionTime = 1;
        UpdateViewAnswerMetrics::dispatch($requestKey)->delay(now()->addMinutes($jobExecutionTime));
    }
}
