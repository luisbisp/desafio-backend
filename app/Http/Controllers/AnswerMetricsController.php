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

		$answersMetrics = AnswersMetrics::where('form_id', $formId)->get();

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

		(new MetricsService())->updateAnswerMetrics($validated);

		return response()->json(['data' => $validated], 200);
	}
}
