<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\memberController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LandInvestmentController;
use App\Http\Controllers\OtherInvestmentController;
use App\Http\Controllers\FinancialEntryController;



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
     Route::get('investments', [InvestmentController::class, 'index'])->name('investments.index');
     Route::get('investments/financial-entries', [FinancialEntryController::class, 'index'])->name('investments.financial-entries.index');
     Route::post('investments/financial-entries', [FinancialEntryController::class, 'store'])->name('investments.financial-entries.store');
     Route::delete('investments/financial-entries/{entry}', [FinancialEntryController::class, 'destroy'])->name('investments.financial-entries.destroy');

     Route::get('investments/loans', [LoanController::class, 'index'])->name('investments.loans.index');
     Route::get('investments/loans/create', [LoanController::class, 'create'])->name('investments.loans.create');
     Route::post('investments/loans', [LoanController::class, 'store'])->name('investments.loans.store');
     Route::get('investments/loans/{loan}/edit', [LoanController::class, 'edit'])->name('investments.loans.edit');
     Route::put('investments/loans/{loan}', [LoanController::class, 'update'])->name('investments.loans.update');
     Route::delete('investments/loans/{loan}', [LoanController::class, 'destroy'])->name('investments.loans.destroy');
     Route::get('investments/loans/{loan}/schedule', [LoanController::class, 'show'])->name('investments.loans.schedule');
     Route::post('investments/loans/{loan}/schedule/{month}/pay', [LoanController::class, 'pay'])->name('investments.loans.pay');
     Route::get('investment-loan-payments/{payment}/receipt', [LoanController::class, 'receipt'])->name('investments.loans.receipt');
     Route::get('loan-collaterals/{collateral}/document', [LoanController::class, 'collateralDocument'])->name('investments.loans.collateral-document');

     Route::get('investments/clients', [LoanController::class, 'clients'])->name('investments.clients.index');
     Route::get('investments/clients/create', [LoanController::class, 'createClient'])->name('investments.clients.create');
     Route::post('investments/clients', [LoanController::class, 'storeClient'])->name('investments.clients.store');
     Route::get('investments/clients/{client}/edit', [LoanController::class, 'editClient'])->name('investments.clients.edit');
     Route::put('investments/clients/{client}', [LoanController::class, 'updateClient'])->name('investments.clients.update');
     Route::delete('investments/clients/{client}', [LoanController::class, 'destroyClient'])->name('investments.clients.destroy');

     Route::get('investments/land', [LandInvestmentController::class, 'index'])->name('investments.land.index');
     Route::get('investments/land/create', [LandInvestmentController::class, 'create'])->name('investments.land.create');
     Route::post('investments/land', [LandInvestmentController::class, 'store'])->name('investments.land.store');
     Route::get('investments/land/{land}/deed', [LandInvestmentController::class, 'deed'])->name('investments.land.deed');
     Route::get('investments/land/{land}/sell', [LandInvestmentController::class, 'sell'])->name('investments.land.sell');
     Route::post('investments/land/{land}/sales', [LandInvestmentController::class, 'storeSale'])->name('investments.land.sales.store');
     Route::get('land-sales/{sale}/deed', [LandInvestmentController::class, 'saleDeed'])->name('investments.land.sales.deed');
     Route::get('investments/land/{land}/edit', [LandInvestmentController::class, 'edit'])->name('investments.land.edit');
     Route::put('investments/land/{land}', [LandInvestmentController::class, 'update'])->name('investments.land.update');
     Route::delete('investments/land/{land}', [LandInvestmentController::class, 'destroy'])->name('investments.land.destroy');

     Route::get('investments/others', [OtherInvestmentController::class, 'index'])->name('investments.others.index');
     Route::get('investments/others/create', [OtherInvestmentController::class, 'create'])->name('investments.others.create');
     Route::post('investments/others', [OtherInvestmentController::class, 'store'])->name('investments.others.store');
     Route::get('other-investments/{other}/receipt', [OtherInvestmentController::class, 'receipt'])->name('investments.others.receipt');
     Route::get('investments/others/{other}/edit', [OtherInvestmentController::class, 'edit'])->name('investments.others.edit');
     Route::put('investments/others/{other}', [OtherInvestmentController::class, 'update'])->name('investments.others.update');
     Route::delete('investments/others/{other}', [OtherInvestmentController::class, 'destroy'])->name('investments.others.destroy');
});


Auth::routes();



