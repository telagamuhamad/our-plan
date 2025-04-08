<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\MeetingApiController;
use App\Http\Controllers\Api\SavingApiController;
use App\Http\Controllers\Api\SavingTransactionApiController;
use App\Http\Controllers\Api\TravelApiController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [AuthApiController::class, 'login'])->name('login');
Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

        // Meetings
        Route::prefix('meetings')->group(function () {
            Route::get('index', [MeetingApiController::class, 'index'])->name('meetings.index');
            Route::get('show/{meetingId}', [MeetingApiController::class, 'show'])->name('meetings.show');
            Route::post('store', [MeetingApiController::class, 'store'])->name('meetings.store');
            Route::put('update/{meetingId}', [MeetingApiController::class, 'update'])->name('meetings.update');
            Route::delete('destroy/{meetingId}', [MeetingApiController::class, 'destroy'])->name('meetings.destroy');
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
        });
});
