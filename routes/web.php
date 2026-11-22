<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\memberController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\SavingsController;



Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
     Route::get('members/deleted', [MemberController::class, 'deleted'])->name('members.deleted');
     Route::post('members/{member}/restore', [MemberController::class, 'restore'])->withTrashed()->name('members.restore');
     Route::resource('members', MemberController::class);
     Route::get('installments', [InstallmentController::class, 'index'])->name('installments.index');
     Route::post('members/{member}/installments', [InstallmentController::class, 'store'])->name('members.installments.store');
     Route::get('installment-payments/{installmentPayment}/receipt', [InstallmentController::class, 'receipt'])->name('installment-payments.receipt');
     Route::get('savings', [SavingsController::class, 'index'])->name('savings.index');
     Route::resource('posts', PostsController::class);
});


Auth::routes();



