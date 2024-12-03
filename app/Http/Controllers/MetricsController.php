<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MetricsController extends Controller
{


    public function update(Request $request, string $type) {

        return match ($type) {
            'answers' => (new AnswerMetricsController())->update($request),
            default => response()->json(['message' => 'Invalid type'], 400)
        };
    
    }

    public function show(string $type, string $formId) {

        return match ($type) {
            'answers' => (new AnswerMetricsController())->show($formId),
            default => response()->json(['message' => 'Invalid type'], 400)
        };

    }

}
