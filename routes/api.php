<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CoupleApiController;
use App\Http\Controllers\Api\DailyMoodApiController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\MeetingApiController;
use App\Http\Controllers\Api\MeetingFeedbackApiController;
use App\Http\Controllers\Api\MissingYouController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SavingApiController;
use App\Http\Controllers\Api\SavingTransactionApiController;
use App\Http\Controllers\Api\TravelJournalApiController;
use App\Http\Controllers\Api\TravelPhotoApiController;
use App\Http\Controllers\SavingCategoryController;
use App\Http\Controllers\RecurringSavingController;
use App\Http\Controllers\SavingsAnalyticsController;
use App\Http\Controllers\SavingsComparisonController;
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

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()->toIso8601String(),
        'version' => app()->version(),
    ]);
})->name('health');

// Public routes
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileApiController::class, 'show'])->name('profile.show');
        Route::put('/', [ProfileApiController::class, 'update']);
        Route::put('/password', [ProfileApiController::class, 'updatePassword']);
        Route::post('/avatar', [ProfileApiController::class, 'updateAvatar']);
        Route::post('/avatar/remove', [ProfileApiController::class, 'removeAvatar']);
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
            Route::get('analytics', [MeetingApiController::class, 'analytics'])->name('meetings.analytics');
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
            Route::get('analytics', [TravelApiController::class, 'analytics'])->name('travels.analytics');
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

            // Travel Photos
            Route::get('{travelId}/photos', [TravelPhotoApiController::class, 'index'])->name('travels.photos.index');
            Route::post('{travelId}/photos', [TravelPhotoApiController::class, 'store'])->name('travels.photos.store');
            Route::post('{travelId}/photos/multiple', [TravelPhotoApiController::class, 'storeMultiple'])->name('travels.photos.store-multiple');
            Route::put('photos/{photoId}', [TravelPhotoApiController::class, 'update'])->name('travels.photos.update');
            Route::delete('photos/{photoId}', [TravelPhotoApiController::class, 'destroy'])->name('travels.photos.destroy');
            Route::post('photos/order', [TravelPhotoApiController::class, 'updateOrder'])->name('travels.photos.order');

            // Travel Journals
            Route::get('{travelId}/journals', [TravelJournalApiController::class, 'byTravel'])->name('travels.journals.index');
        });

        // Travel Journals (separate section)
        Route::prefix('journals')->group(function () {
            Route::get('/', [TravelJournalApiController::class, 'index'])->name('journals.index');
            Route::post('/', [TravelJournalApiController::class, 'store'])->name('journals.store');
            Route::get('/{journalId}', [TravelJournalApiController::class, 'show'])->name('journals.show');
            Route::put('/{journalId}', [TravelJournalApiController::class, 'update'])->name('journals.update');
            Route::delete('/{journalId}', [TravelJournalApiController::class, 'destroy'])->name('journals.destroy');
            Route::post('/{journalId}/favorite', [TravelJournalApiController::class, 'toggleFavorite'])->name('journals.favorite');
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

        // Recurring Savings
        Route::prefix('recurring-savings')->group(function () {
            Route::get('/', [RecurringSavingController::class, 'index'])->name('recurring-savings.index');
            Route::get('/stats', [RecurringSavingController::class, 'stats'])->name('recurring-savings.stats');
            Route::post('/', [RecurringSavingController::class, 'store'])->name('recurring-savings.store');
            Route::get('/{id}', [RecurringSavingController::class, 'show'])->name('recurring-savings.show');
            Route::put('/{id}', [RecurringSavingController::class, 'update'])->name('recurring-savings.update');
            Route::delete('/{id}', [RecurringSavingController::class, 'destroy'])->name('recurring-savings.destroy');
            Route::post('/{id}/pause', [RecurringSavingController::class, 'pause'])->name('recurring-savings.pause');
            Route::post('/{id}/resume', [RecurringSavingController::class, 'resume'])->name('recurring-savings.resume');
            Route::post('/{id}/skip', [RecurringSavingController::class, 'skip'])->name('recurring-savings.skip');
        });

        // Savings Analytics
        Route::prefix('savings-analytics')->group(function () {
            Route::get('/overview', [SavingsAnalyticsController::class, 'overview'])->name('savings-analytics.overview');
            Route::get('/trends', [SavingsAnalyticsController::class, 'trends'])->name('savings-analytics.trends');
            Route::get('/goals', [SavingsAnalyticsController::class, 'goals'])->name('savings-analytics.goals');
            Route::get('/growth', [SavingsAnalyticsController::class, 'growth'])->name('savings-analytics.growth');
            Route::get('/categories', [SavingsAnalyticsController::class, 'categories'])->name('savings-analytics.categories');
            Route::get('/upcoming', [SavingsAnalyticsController::class, 'upcoming'])->name('savings-analytics.upcoming');
            Route::get('/compare', [SavingsAnalyticsController::class, 'compare'])->name('savings-analytics.compare');
            Route::get('/export', [SavingsAnalyticsController::class, 'export'])->name('savings-analytics.export');
        });

        // Savings Comparison with Partner
        Route::prefix('savings-comparison')->group(function () {
            Route::get('/overview', [SavingsComparisonController::class, 'overview'])->name('savings-comparison.overview');
            Route::get('/savings-list', [SavingsComparisonController::class, 'savingsList'])->name('savings-comparison.savings-list');
            Route::get('/monthly-contributions', [SavingsComparisonController::class, 'monthlyContributions'])->name('savings-comparison.monthly-contributions');
            Route::get('/categories', [SavingsComparisonController::class, 'categories'])->name('savings-comparison.categories');
            Route::get('/goals', [SavingsComparisonController::class, 'goals'])->name('savings-comparison.goals');
            Route::get('/achievements', [SavingsComparisonController::class, 'achievements'])->name('savings-comparison.achievements');
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
            Route::post('/{id}/mark-completed', [GoalController::class, 'markCompleted'])->name('goals.mark-completed');
            Route::post('/{id}/mark-in-progress', [GoalController::class, 'markInProgress'])->name('goals.mark-in-progress');
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

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationApiController::class, 'index'])->name('notifications.index');
            Route::get('/unread-count', [NotificationApiController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::post('/{id}/mark-read', [NotificationApiController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::post('/mark-all-read', [NotificationApiController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
            Route::delete('/{id}', [NotificationApiController::class, 'destroy'])->name('notifications.destroy');
        });
    });
});
