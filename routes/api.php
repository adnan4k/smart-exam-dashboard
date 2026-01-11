<?php

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\PasswordResetController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    
    // Get the current token being used
    $currentToken = $request->user()->currentAccessToken();
    
    return response()->json([
        'user' => $user,
        'current_token' => [
            'id' => $currentToken->id,
            'name' => $currentToken->name,
            'abilities' => $currentToken->abilities,
            'last_used_at' => $currentToken->last_used_at,
        ]
    ]);
});

// Public routes
Route::post('subjects',[QuestionController::class,'availableSubjects']);
Route::post('/available-chapters', [QuestionController::class, 'availableChapters']);
Route::post('check-subscription', [SubscriptionController::class, 'checkSubscription']);
Route::post('questions/by-year', [QuestionController::class, 'getQuestionsByYear']);
Route::post('sample-questions', [QuestionController::class, 'sampleQuestions']);
Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
Route::post('register',[UserController::class,'register']);
Route::post('login', [UserController::class,'login']);

// Password reset routes
Route::post('auth/password-reset/request', [PasswordResetController::class, 'request']);
Route::post('auth/password-reset/verify', [PasswordResetController::class, 'verify']);
Route::post('auth/password-reset/confirm', [PasswordResetController::class, 'confirm']);  
Route::get('/exam-type',[QuestionController::class,'examType']);
Route::get('/questions/year', [QuestionController::class, 'getQuestionsByYear']);
Route::get('/questions/subject', [QuestionController::class, 'getQuestionsBySubject']);
Route::get('/questions/grouped-by-type', [QuestionController::class, 'getAllQuestionsGroupedByType']);
Route::get('/questions/grouped-by-subject', [QuestionController::class, 'getAllQuestionsGroupedBySubject']);
Route::post('check-subscription', [SubscriptionController::class, 'checkSubscription']);
Route::post('fcm/register-token', [NotificationController::class, 'registerToken']);
Route::get('notifications', [NotificationController::class, 'index']);
Route::post('notifications', [NotificationController::class, 'store']);
Route::post('notifications/{notification}/like', [NotificationController::class, 'like']);
Route::post('notifications/{notification}/dislike', [NotificationController::class, 'dislike']);
Route::post('notifications/{notification}/comment', [NotificationController::class, 'comment']);

// Notes routes - specific routes first to avoid conflicts
Route::get('notes/for-user', [NoteController::class, 'forUser']);
Route::get('notes/for-user-grouped', [NoteController::class, 'forUserGrouped']);
Route::get('notes/filter', [NoteController::class, 'filter']);

// General notes routes
Route::get('notes', [NoteController::class, 'index']);
Route::post('notes', [NoteController::class, 'store']);
Route::get('notes/{note}', [NoteController::class, 'show']);
Route::put('notes/{note}', [NoteController::class, 'update']);
Route::patch('notes/{note}', [NoteController::class, 'update']);
Route::delete('notes/{note}', [NoteController::class, 'destroy']);

// Referral endpoints (using query parameters)
Route::get('my-referrals', [UserController::class, 'getMyReferrals']);
Route::get('referral-details', [UserController::class, 'getReferralDetails']);

// Public token generation endpoint (for getting tokens for specific users)
Route::post('get-user-token', [UserController::class, 'getUserToken']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout']);
    
    // Token management routes
    Route::get('my-tokens', [UserController::class, 'getMyTokens']);
    Route::post('create-token', [UserController::class, 'createToken']);
    Route::delete('revoke-token/{tokenId}', [UserController::class, 'revokeToken']);
});

// Public question routes (no authentication)
Route::get('/questions/type', [QuestionController::class, 'getQuestionsByType']);
Route::post('get-questions', [QuestionController::class, 'getQuestionsByType']);
