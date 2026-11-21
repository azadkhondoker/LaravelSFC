<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\memberController;
use App\Http\Controllers\PostsController;



Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
     Route::resource('members', MemberController::class)->except('show');
     Route::resource('posts', PostsController::class);
});


Auth::routes();



