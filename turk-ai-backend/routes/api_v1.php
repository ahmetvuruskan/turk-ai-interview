<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['jwtAuth', 'role:ROLE_USER|ROLE_ADMIN'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'update']);
    });
});

Route::middleware(['jwtAuth', 'role:ROLE_ADMIN'])->group(function () {
    Route::get('students', [StudentController::class, 'list']);
    Route::post('students/{student}/code', [StudentController::class, 'assignCode']);
});


