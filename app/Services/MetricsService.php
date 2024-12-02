<?php

namespace App\Services;

use App\Jobs\UpdateViewAnswerMetrics;
use App\Models\AnswersMetrics;
use App\Models\FormMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

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
            'view' => $newMetric->increment('views', $answer['increments']),
        };
    }

    public function synchronizeAnswerMetrics($oldFields, $updatedFields)
    {

        $oldIds = collect($oldFields)->pluck('field_id')->toArray();
        $newIds = collect($updatedFields)->pluck('field_id')->toArray();

        $removedIds = array_diff($oldIds, $newIds);

        foreach ($removedIds as $id) {
            AnswersMetrics::where('field_id', $id)->delete();
        }
    }

    public function createAnswerMetricsJob($data)
    {
        $requestKey = 'answer_metrics_' . $data['form_id'] . '_' . $data['field_id'];
        $existingData = Redis::get($requestKey);

        if ($existingData) {

            $data = json_decode($existingData, true);
            $data['increments']++;
            Redis::set($requestKey, json_encode($data));

        } else {

            $expirationTime = 300;
            $data['increments'] = 1;
            Redis::setex($requestKey, $expirationTime, json_encode($data));

            $jobExecutionTime = 1;
            UpdateViewAnswerMetrics::dispatch($requestKey)->delay(now()->addMinutes($jobExecutionTime));
        }
    }
}
