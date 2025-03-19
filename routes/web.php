<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\SavingTransactionController;
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

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Meetings
    Route::prefix('meetings')->group(function () {
        Route::get('index', [MeetingController::class, 'index'])->name('meetings.index');
        Route::get('show/{meetingId}', [MeetingController::class, 'show'])->name('meetings.show');
        Route::get('create', [MeetingController::class, 'create'])->name('meetings.create');
        Route::post('store', [MeetingController::class, 'store'])->name('meetings.store');
        Route::get('edit/{meetingId}', [MeetingController::class, 'edit'])->name('meetings.edit');
        Route::put('update/{meetingId}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::delete('destroy/{meetingId}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
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
});
