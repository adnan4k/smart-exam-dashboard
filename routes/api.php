<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('subjects',[QuestionController::class,'availableSubjects']);
Route::post('/available-chapters', [QuestionController::class, 'availableChapters']);

Route::post('check-subscription', [SubscriptionController::class, 'checkSubscription']);
Route::post('questions/by-year', [QuestionController::class, 'getQuestionsByYear']);
Route::post('sample-questions', [QuestionController::class, 'sampleQuestions']);
Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
Route::post('register',[UserController::class,'register']);
Route::get('/exam-type',[QuestionController::class,'examType']);
Route::get('/questions/year', [QuestionController::class, 'getQuestionsByYear']);
Route::get('/questions/subject', [QuestionController::class, 'getQuestionsBySubject']);
Route::get('/questions/type', [QuestionController::class, 'getQuestionsByType']);
Route::get('/questions/grouped-by-type', [QuestionController::class, 'getAllQuestionsGroupedByType']);
Route::get('/questions/grouped-by-subject', [QuestionController::class, 'getAllQuestionsGroupedBySubject']);
Route::post('check-subscription', [SubscriptionController::class, 'checkSubscription']);
