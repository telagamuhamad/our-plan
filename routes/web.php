<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
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
        Route::get('create', [MeetingController::class, 'create'])->name('meetings.create');
        Route::post('store', [MeetingController::class, 'store'])->name('meetings.store');
        Route::get('edit/{meetingId}', [MeetingController::class, 'edit'])->name('meetings.edit');
        Route::put('update/{meetingId}', [MeetingController::class, 'update'])->name('meetings.update');
        Route::delete('destroy/{meetingId}', [MeetingController::class, 'destroy'])->name('meetings.destroy');
    });
});
