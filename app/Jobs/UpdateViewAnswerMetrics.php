<?php

namespace App\Jobs;

use App\Services\MetricsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

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
        $answerData = Redis::get($this->requestKey);

        if ($answerData) {
            $answerData = json_decode($answerData, true);

            (new MetricsService())->updateAnswerMetrics($answerData);
       
            Redis::del($this->requestKey);
        }
    }
}
