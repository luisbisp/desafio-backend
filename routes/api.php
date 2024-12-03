<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AnswerMetricsController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\RespondentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * 🌍 Public Routes
 */
Route::post('users', [UserController::class, 'store'])->name("users.store");
Route::post('answers', [AnswerController::class, 'store'])->name("answers.store");
Route::put('answers', [AnswerController::class, 'update'])->name("answers.update");
Route::get('forms/{form}', [FormController::class, 'show'])->name("forms.show");
Route::post('metrics/{type}', [MetricsController::class, 'update'])->name("metrics.update");

/** 🗝️ Pivate routes */
Route::middleware('auth:sanctum')->group(function (){
    Route::apiResource('users', UserController::class)->except(["store"]);
    Route::apiResource('forms', FormController::class)->except('show');
    Route::apiResource('answers', AnswerController::class)->except(["store", "update"]);
    Route::apiResource('respondents', RespondentController::class);
    
    Route::get('metrics/{type}/{form}', [MetricsController::class, 'show'])->name("metrics.show");
});


