<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CoupleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyMoodController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MeetingAnalyticsController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingFeedbackController;
use App\Http\Controllers\MissingYouController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\SavingTransactionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TravelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Pairing Routes (accessible without couple)
    Route::prefix('pairing')->group(function () {
        Route::get('create-invite', [CoupleController::class, 'showCreateInvite'])
            ->name('pairing.create-invite')
            ->middleware('pairing.throttle:3,10');

        Route::post('create-invite', [CoupleController::class, 'storeInviteCode'])
            ->name('pairing.store-invite');

        Route::get('join', [CoupleController::class, 'showJoin'])
            ->name('pairing.join');

        Route::post('join', [CoupleController::class, 'join'])
            ->middleware('pairing.throttle:5,10');

        Route::get('status', [CoupleController::class, 'showStatus'])
            ->name('pairing.status');

        Route::post('confirm', [CoupleController::class, 'confirm'])
            ->name('pairing.confirm');

        Route::post('leave', [CoupleController::class, 'leave'])
            ->name('pairing.leave');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
        Route::post('/avatar/remove', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    });

    // Routes that require active couple membership
    Route::middleware('belongs.to.couple')->group(function () {
        // Meetings
        Route::prefix('meetings')->group(function () {
            Route::get('index', [MeetingController::class, 'index'])->name('meetings.index');
            Route::get('countdown', [MeetingController::class, 'countdown'])->name('meetings.countdown');
            Route::get('analytics', [MeetingAnalyticsController::class, 'index'])->name('meetings.analytics');
            Route::get('analytics-data', [MeetingAnalyticsController::class, 'data'])->name('meetings.analytics.data');
            Route::get('show/{meetingId}', [MeetingController::class, 'show'])->name('meetings.show');
            Route::get('create', [MeetingController::class, 'create'])->name('meetings.create');
            Route::post('store', [MeetingController::class, 'store'])->name('meetings.store');
            Route::get('edit/{meetingId}', [MeetingController::class, 'edit'])->name('meetings.edit');
            Route::put('update/{meetingId}', [MeetingController::class, 'update'])->name('meetings.update');
            Route::delete('destroy/{meetingId}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

            // Meeting Feedback
            Route::get('{meetingId}/feedback', [MeetingFeedbackController::class, 'index'])->name('meetings.feedback.index');
            Route::post('{meetingId}/feedback', [MeetingFeedbackController::class, 'store'])->name('meetings.feedback.store');
            Route::put('feedback/{feedbackId}', [MeetingFeedbackController::class, 'update'])->name('meetings.feedback.update');
            Route::delete('feedback/{feedbackId}', [MeetingFeedbackController::class, 'destroy'])->name('meetings.feedback.destroy');
            Route::get('{meetingId}/feedback/can-give', [MeetingFeedbackController::class, 'canGiveFeedback'])->name('meetings.feedback.can-give');
        });

        // Travels
        Route::prefix('travels')->group(function () {
            Route::get('index', [TravelController::class, 'index'])->name('travels.index');
            Route::get('show/{travelId}', [TravelController::class, 'show'])->name('travels.show');
            Route::get('create', [TravelController::class, 'create'])->name('travels.create');
            Route::post('store', [TravelController::class, 'store'])->name('travels.store');
            Route::get('edit/{travelId}', [TravelController::class, 'edit'])->name('travels.edit');
            Route::put('update/{travelId}', [TravelController::class, 'update'])->name('travels.update');
            Route::delete('destroy/{travelId}', [TravelController::class, 'destroy'])->name('travels.destroy');
            Route::post('assign-to-meeting/{meetingId}', [TravelController::class, 'assignToMeeting'])->name('travels.assign-to-meeting');
            Route::patch('complete-travel/{travelId}', [TravelController::class, 'completeTravel'])->name('travels.complete-travel');
            Route::patch('remove-from-meeting/{travelId}', [TravelController::class, 'removeFromMeeting'])->name('travels.remove-from-meeting');
        });

        // Savings
        Route::prefix('savings')->group(function () {
            Route::get('index', [SavingController::class, 'index'])->name('savings.index');
            Route::get('create', [SavingController::class, 'create'])->name('savings.create');
            Route::post('store', [SavingController::class, 'store'])->name('savings.store');
            Route::get('show/{savingId}', [SavingController::class, 'show'])->name('savings.show');
            Route::get('edit/{savingId}', [SavingController::class, 'edit'])->name('savings.edit');
            Route::put('update/{savingId}', [SavingController::class, 'update'])->name('savings.update');
            Route::delete('destroy/{savingId}', [SavingController::class, 'destroy'])->name('savings.destroy');
            Route::post('{savingId}/transactions', [SavingTransactionController::class, 'store'])->name('savings.transactions.store');
            Route::get('/transfer', [SavingController::class, 'showTransferForm'])->name('savings.transfer.form');
            Route::post('/transfer', [SavingController::class, 'transfer'])->name('savings.transfer');
        });

        // Timeline
        Route::prefix('timeline')->group(function () {
            Route::get('index', [TimelineController::class, 'index'])->name('timeline.index');
            Route::get('create', [TimelineController::class, 'create'])->name('timeline.create');
            Route::post('store', [TimelineController::class, 'store'])->name('timeline.store');
            Route::get('show/{postId}', [TimelineController::class, 'show'])->name('timeline.show');
            Route::get('edit/{postId}', [TimelineController::class, 'edit'])->name('timeline.edit');
            Route::put('update/{postId}', [TimelineController::class, 'update'])->name('timeline.update');
            Route::delete('destroy/{postId}', [TimelineController::class, 'destroy'])->name('timeline.destroy');
            Route::post('react/{postId}', [TimelineController::class, 'react'])->name('timeline.react');
            Route::delete('unreact/{postId}', [TimelineController::class, 'unreact'])->name('timeline.unreact');
            Route::post('comment/{postId}', [TimelineController::class, 'comment'])->name('timeline.comment');
            Route::delete('comment/{commentId}', [TimelineController::class, 'deleteComment'])->name('timeline.delete-comment');
            Route::get('load-more', [TimelineController::class, 'loadMore'])->name('timeline.load-more');
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('index', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
            Route::post('mark-read/{notificationId}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
            Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
            Route::delete('destroy/{notificationId}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        });

        // Mood Check-In
        Route::prefix('mood')->group(function () {
            Route::get('/', [DailyMoodController::class, 'index'])->name('mood.index');
            Route::post('/check-in', [DailyMoodController::class, 'checkIn'])->name('mood.check-in');
            Route::put('/update', [DailyMoodController::class, 'update'])->name('mood.update');
            Route::get('/stats', [DailyMoodController::class, 'stats'])->name('mood.stats');
        });

        // Missing You
        Route::prefix('missing-you')->group(function () {
            Route::get('/', [MissingYouController::class, 'index'])->name('missing-you.index');
            Route::post('/send', [MissingYouController::class, 'send'])->name('missing-you.send');
            Route::get('/status', [MissingYouController::class, 'status'])->name('missing-you.status');
            Route::get('/templates', [MissingYouController::class, 'templates'])->name('missing-you.templates');
        });

        // Question of the Day
        Route::prefix('questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index'])->name('questions.index');
            Route::post('/answer', [QuestionController::class, 'answer'])->name('questions.answer');
            Route::put('/update', [QuestionController::class, 'update'])->name('questions.update');
            Route::post('/answer-mode', [QuestionController::class, 'setAnswerMode'])->name('questions.set-answer-mode');
            Route::get('/{date}', [QuestionController::class, 'show'])->name('questions.show');
        });

        // Goals
        Route::prefix('goals')->group(function () {
            Route::get('/', [GoalController::class, 'index'])->name('goals.index');
            Route::get('/create', [GoalController::class, 'create'])->name('goals.create');
            Route::post('/', [GoalController::class, 'store'])->name('goals.store');
            Route::get('/stats', [GoalController::class, 'stats'])->name('goals.stats');
            Route::get('/{id}', [GoalController::class, 'show'])->name('goals.show');
            Route::get('/{id}/edit', [GoalController::class, 'edit'])->name('goals.edit');
            Route::put('/{id}', [GoalController::class, 'update'])->name('goals.update');
            Route::delete('/{id}', [GoalController::class, 'destroy'])->name('goals.destroy');
            Route::post('/{id}/mark-completed', [GoalController::class, 'markCompleted'])->name('goals.mark-completed');
            Route::post('/{id}/mark-in-progress', [GoalController::class, 'markInProgress'])->name('goals.mark-in-progress');
        });

        // Tasks
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
            Route::get('/create', [TaskController::class, 'create'])->name('tasks.create');
            Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
            Route::get('/stats', [TaskController::class, 'stats'])->name('tasks.stats');
            Route::get('/pending', [TaskController::class, 'pending'])->name('tasks.pending');
            Route::get('/my-tasks', [TaskController::class, 'myTasks'])->name('tasks.my-tasks');
            Route::get('/{id}', [TaskController::class, 'show'])->name('tasks.show');
            Route::get('/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
            Route::put('/{id}', [TaskController::class, 'update'])->name('tasks.update');
            Route::post('/toggle/{id}', [TaskController::class, 'toggle'])->name('tasks.toggle');
            Route::delete('/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        });
    });
});
