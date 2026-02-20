<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CoupleApiController;
use App\Http\Controllers\Api\DailyMoodApiController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\MeetingApiController;
use App\Http\Controllers\Api\MeetingFeedbackApiController;
use App\Http\Controllers\Api\MissingYouController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SavingApiController;
use App\Http\Controllers\Api\SavingTransactionApiController;
use App\Http\Controllers\SavingCategoryController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TimelineApiController;
use App\Http\Controllers\Api\TravelApiController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public routes
Route::post('/register', [AuthApiController::class, 'register'])->name('register');
Route::post('/login', [AuthApiController::class, 'login'])->name('login');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    // Pairing Routes
    Route::prefix('pairing')->group(function () {
        Route::post('/create-invite', [CoupleApiController::class, 'createInviteCode'])
            ->middleware('pairing.throttle:3,10');

        Route::post('/join', [CoupleApiController::class, 'joinCouple'])
            ->middleware('pairing.throttle:5,10');

        Route::get('/status', [CoupleApiController::class, 'getStatus']);

        Route::post('/confirm', [CoupleApiController::class, 'confirmPairing']);

        Route::post('/leave', [CoupleApiController::class, 'leaveCouple']);

        Route::get('/', [CoupleApiController::class, 'show']);
    });

    // Routes that require active couple membership
    Route::middleware('belongs.to.couple')->group(function () {
        // Meetings
        Route::prefix('meetings')->group(function () {
            Route::get('index', [MeetingApiController::class, 'index'])->name('meetings.index');
            Route::get('countdown', [MeetingApiController::class, 'countdown'])->name('meetings.countdown');
            Route::get('show/{meetingId}', [MeetingApiController::class, 'show'])->name('meetings.show');
            Route::post('store', [MeetingApiController::class, 'store'])->name('meetings.store');
            Route::put('update/{meetingId}', [MeetingApiController::class, 'update'])->name('meetings.update');
            Route::delete('destroy/{meetingId}', [MeetingApiController::class, 'destroy'])->name('meetings.destroy');

            // Meeting Feedback
            Route::get('{meetingId}/feedback', [MeetingFeedbackApiController::class, 'index'])->name('meetings.feedback.index');
            Route::post('{meetingId}/feedback', [MeetingFeedbackApiController::class, 'store'])->name('meetings.feedback.store');
            Route::put('feedback/{feedbackId}', [MeetingFeedbackApiController::class, 'update'])->name('meetings.feedback.update');
            Route::delete('feedback/{feedbackId}', [MeetingFeedbackApiController::class, 'destroy'])->name('meetings.feedback.destroy');
            Route::get('{meetingId}/feedback/can-give', [MeetingFeedbackApiController::class, 'canGiveFeedback'])->name('meetings.feedback.can-give');
        });

        // Travels
        Route::prefix('travels')->group(function () {
            Route::get('index', [TravelApiController::class, 'index'])->name('travels.index');
            Route::get('show/{travelId}', [TravelApiController::class, 'show'])->name('travels.show');
            Route::post('store', [TravelApiController::class, 'store'])->name('travels.store');
            Route::put('update/{travelId}', [TravelApiController::class, 'update'])->name('travels.update');
            Route::delete('destroy/{travelId}', [TravelApiController::class, 'destroy'])->name('travels.destroy');
            Route::post('assign-to-meeting/{meetingId}', [TravelApiController::class, 'assignToMeeting'])->name('travels.assign-to-meeting');
            Route::patch('complete-travel/{travelId}', [TravelApiController::class, 'completeTravel'])->name('travels.complete-travel');
            Route::patch('remove-from-meeting/{travelId}', [TravelApiController::class, 'removeFromMeeting'])->name('travels.remove-from-meeting');

            // Additional apis
            Route::get('get-unassigned-travels', [TravelApiController::class, 'getUnassignedTravels'])->name('travels.get-unassigned-travels');
            Route::put('update-visit-date/{travelId}', [TravelApiController::class, 'updateVisitDate'])->name('travels.update-visit-date');
        });

        // Savings
        Route::prefix('savings')->group(function () {
            Route::get('index', [SavingApiController::class, 'index'])->name('savings.index');
            Route::post('store', [SavingApiController::class, 'store'])->name('savings.store');
            Route::get('show/{savingId}', [SavingApiController::class, 'show'])->name('savings.show');
            Route::put('update/{savingId}', [SavingApiController::class, 'update'])->name('savings.update');
            Route::delete('destroy/{savingId}', [SavingApiController::class, 'destroy'])->name('savings.destroy');
            Route::post('{savingId}/transactions', [SavingTransactionApiController::class, 'store'])->name('savings.transactions.store');
            Route::post('/transfer', [SavingApiController::class, 'transfer'])->name('savings.transfer');
            Route::get('upcoming-deadlines', [SavingApiController::class, 'upcomingDeadlines'])->name('savings.upcoming-deadlines');
            Route::get('overdue', [SavingApiController::class, 'overdue'])->name('savings.overdue');
            Route::post('{savingId}/mark-completed', [SavingApiController::class, 'markCompleted'])->name('savings.mark-completed');
        });

        // Saving Categories
        Route::prefix('saving-categories')->group(function () {
            Route::get('/', [SavingCategoryController::class, 'index'])->name('saving-categories.index');
            Route::post('/', [SavingCategoryController::class, 'store'])->name('saving-categories.store');
            Route::put('/{id}', [SavingCategoryController::class, 'update'])->name('saving-categories.update');
            Route::delete('/{id}', [SavingCategoryController::class, 'destroy'])->name('saving-categories.destroy');
        });

        // Timeline
        Route::prefix('timeline')->group(function () {
            Route::get('index', [TimelineApiController::class, 'index'])->name('timeline.index');
            Route::get('show/{postId}', [TimelineApiController::class, 'show'])->name('timeline.show');
            Route::post('store', [TimelineApiController::class, 'store'])->name('timeline.store');
            Route::post('update/{postId}', [TimelineApiController::class, 'update'])->name('timeline.update');
            Route::delete('destroy/{postId}', [TimelineApiController::class, 'destroy'])->name('timeline.destroy');
            Route::post('react/{postId}', [TimelineApiController::class, 'react'])->name('timeline.react');
            Route::delete('unreact/{postId}', [TimelineApiController::class, 'unreact'])->name('timeline.unreact');
            Route::post('comment/{postId}', [TimelineApiController::class, 'comment'])->name('timeline.comment');
            Route::get('comments/{postId}', [TimelineApiController::class, 'comments'])->name('timeline.comments');
            Route::delete('comment/{commentId}', [TimelineApiController::class, 'deleteComment'])->name('timeline.delete-comment');
        });

        // Mood Check-In
        Route::prefix('mood')->group(function () {
            Route::get('/', [DailyMoodApiController::class, 'index'])->name('mood.index');
            Route::post('/', [DailyMoodApiController::class, 'store'])->name('mood.store');
            Route::get('/today', [DailyMoodApiController::class, 'today'])->name('mood.today');
            Route::get('/stats', [DailyMoodApiController::class, 'stats'])->name('mood.stats');
            Route::put('/{id}', [DailyMoodApiController::class, 'update'])->name('mood.update');
            Route::delete('/{id}', [DailyMoodApiController::class, 'destroy'])->name('mood.destroy');
        });

        // Missing You
        Route::prefix('missing-you')->group(function () {
            Route::get('/', [MissingYouController::class, 'index'])->name('missing-you.index');
            Route::post('/', [MissingYouController::class, 'store'])->name('missing-you.store');
            Route::get('/status', [MissingYouController::class, 'status'])->name('missing-you.status');
            Route::get('/templates', [MissingYouController::class, 'templates'])->name('missing-you.templates');
        });

        // Question of the Day
        Route::prefix('questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index'])->name('questions.index');
            Route::get('/today', [QuestionController::class, 'today'])->name('questions.today');
            Route::post('/answer', [QuestionController::class, 'answer'])->name('questions.answer');
            Route::put('/answer', [QuestionController::class, 'updateAnswer'])->name('questions.update-answer');
            Route::get('/stats', [QuestionController::class, 'stats'])->name('questions.stats');
            Route::get('/categories', [QuestionController::class, 'categories'])->name('questions.categories');
            Route::get('/answer-modes', [QuestionController::class, 'answerModes'])->name('questions.answer-modes');
            Route::post('/answer-mode', [QuestionController::class, 'setAnswerMode'])->name('questions.set-answer-mode');
            Route::get('/{date}', [QuestionController::class, 'show'])->name('questions.show');
        });

        // Goals
        Route::prefix('goals')->group(function () {
            Route::get('/', [GoalController::class, 'index'])->name('goals.index');
            Route::post('/', [GoalController::class, 'store'])->name('goals.store');
            Route::get('/stats', [GoalController::class, 'stats'])->name('goals.stats');
            Route::get('/upcoming', [GoalController::class, 'upcoming'])->name('goals.upcoming');
            Route::get('/{id}', [GoalController::class, 'show'])->name('goals.show');
            Route::put('/{id}', [GoalController::class, 'update'])->name('goals.update');
            Route::delete('/{id}', [GoalController::class, 'destroy'])->name('goals.destroy');
        });

        // Tasks
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
            Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
            Route::get('/pending', [TaskController::class, 'pending'])->name('tasks.pending');
            Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my-tasks');
            Route::get('/stats', [TaskController::class, 'stats'])->name('tasks.stats');
            Route::get('/{id}', [TaskController::class, 'show'])->name('tasks.show');
            Route::put('/{id}', [TaskController::class, 'update'])->name('tasks.update');
            Route::post('/toggle/{id}', [TaskController::class, 'toggle'])->name('tasks.toggle');
            Route::delete('/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        });
    });
});
