<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeaveController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/login', function () {
    return view('auth.login');
})->name('login.form');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Debug routes (temporary - bisa dihapus setelah development)
Route::get('/debug/attendances', function() {
    $attendances = \App\Models\Attendance::all();
    return response()->json($attendances);
});

Route::get('/debug/test-attendance', function() {
    $user = \App\Models\User::first();
    
    $attendance = new \App\Models\Attendance();
    $attendance->user_id = $user->id;
    $attendance->date = now()->format('Y-m-d');
    $attendance->time_in = now()->format('H:i:s');
    $attendance->lat_long_in = '-6.2,106.8';
    $attendance->status_masuk = 'Tepat Waktu';
    $attendance->save();
    
    return "Test attendance created! ID: " . $attendance->id;
});

// Protected Routes - Hanya bisa diakses setelah login
Route::middleware(['auth'])->group(function () {
    // Main Routes
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Attendance Routes - INI YANG DIPERBAIKI
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    
    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Leave Routes
    Route::get('/izin', [LeaveController::class, 'izin'])->name('izin');
    Route::get('/sakit', [LeaveController::class, 'sakit'])->name('sakit');
    Route::get('/cuti', [LeaveController::class, 'cuti'])->name('cuti');
    Route::get('/leave/{id}/edit', [LeaveController::class, 'edit'])->name('leave.edit');
    Route::post('/leave', [LeaveController::class, 'create'])->name('leave.create');
    Route::delete('/leave/{id}', [LeaveController::class, 'destroy'])->name('leave.destroy');
    Route::patch('/leave/{id}/status', [LeaveController::class, 'updateStatus']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});