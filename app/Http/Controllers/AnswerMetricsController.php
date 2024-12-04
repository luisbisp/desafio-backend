<?php

namespace App\Http\Controllers;

use App\Models\AnswersMetrics;
use App\Models\Form;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

		(new MetricsService())->createAnswerMetricsJob($validated);

		return response()->json(['message' => 'Data processed.'], 200);
	}
}
