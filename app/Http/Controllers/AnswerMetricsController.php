<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateViewAnswerMetrics;
use App\Models\AnswersMetrics;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redis;

class AnswerMetricsController extends Controller
{
	public function show($formId)
	{

		$form = Form::where('slug', $formId)->first();

		if (Gate::denies('metrics:view', $form)) {
			return response([], 404);
		};

		$answersMetrics = AnswersMetrics::where('form_id', $formId)
			->get()->each->calculateDropOff();

		return $answersMetrics;
	}

	public function update(Request $request)
	{

		$validated = $request->validate([
			'form_id' => 'required|string|exists:forms,slug',
			'field_id' => 'required|string',
			'type' => 'required|in:view',
		]);

		$form = Form::where('slug', $validated['form_id'])->first();
		$fieldExists = collect($form->fields)
			->contains(fn($field) => $field['field_id'] === $validated['field_id']);

		if (!$fieldExists) {
			return response()->json(['error' => 'Invalid field_id for the provided form_id'], 422);
		}

		$requestKey = 'answer_metrics_' . uniqid();

		$expirationTime = 300;
		Redis::setex($requestKey, $expirationTime, json_encode($validated));

		$jobExecutionTime = 1;
		UpdateViewAnswerMetrics::dispatch($requestKey)->delay(now()->addMinutes($jobExecutionTime));

		return response()->json(['message' => 'Data processed.'], 200);
	}
}
