<?php

namespace App\Jobs;

use App\Models\Form;
use App\Services\MetricsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Enums\AnswerMetricsType;

class UpdateViewAnswerMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestKey;

    public function __construct($requestKey)
    {
        $this->requestKey = $requestKey;
    }

    public function handle()
    {

        $name = $this->requestKey;
        Log::warning($name);
        if(!$name || !str_contains($name, 'metrics:')) {
            Redis::del($this->requestKey);
            return;
        }

        $value = Redis::get($this->requestKey);        

        if (!$value) {
            return;
        }

        $data = explode(':', $this->requestKey);
        $formId = $data[1] ?? null;
        $fieldId = $data[2] ?? null;

        if (!$formId || !$fieldId) {
            return;
        }

        $form = Form::where('slug', $formId)->first();
        $fieldExists = collect($form->fields)->contains(fn($field) => $field['field_id'] === $fieldId);

        if (!$fieldExists) {
            return;
        }

        (new MetricsService())->updateAnswerMetrics(AnswerMetricsType::VIEW, [
            'form_id' => $formId,
            'field_id' => $fieldId,
            'increments' => $value
        ]);

        Redis::del($this->requestKey);
    }
}
