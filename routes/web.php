<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;

Route::view('/', 'welcome')->name('home');
Route::post('/login', [AuthController::class, 'store'])->name('login');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/confirm-print', [AuthController::class, 'confirmPrint'])->name('print.confirm');
    Route::get('/dashboard', [LoanController::class, 'dashboard'])->name('dashboard');
    Route::get('/borrowers', [LoanController::class, 'borrowers'])->name('borrowers.index');
    Route::get('/borrowers/{borrower}', [LoanController::class, 'borrower'])->name('borrowers.show');
    Route::get('/borrowers/{borrower}/identity', [LoanController::class, 'downloadBorrowerId'])->name('borrowers.identity');
    Route::get('/search/suggestions', [LoanController::class, 'searchSuggestions'])->name('search.suggestions');
    Route::get('/search', [LoanController::class, 'search'])->name('search.index');
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans/drafts', [LoanController::class, 'saveDraft'])->name('loans.drafts.store');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/preview', [LoanController::class, 'preview'])->name('loans.preview');
    Route::post('/loans/preview/draft', [LoanController::class, 'savePreviewDraft'])->name('loans.preview.draft');
    Route::post('/loans/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::get('/loans/{loan}/disclosure', [LoanController::class, 'disclosure'])->name('loans.disclosure');
    Route::get('/loans/{loan}/contract', [LoanController::class, 'contract'])->name('loans.contract');
    Route::get('/loans/{loan}/promissory-note', [LoanController::class, 'promissoryNote'])->name('loans.promissory-note');
    Route::get('/loans/{loan}/identity/{participant}', [LoanController::class, 'downloadLoanParticipantId'])->name('loans.identity');
    Route::get('/payments', [LoanController::class, 'payments'])->name('payments.index');
    Route::post('/payments', [LoanController::class, 'recordPayment'])->name('payments.store');
    Route::get('/contracts', [LoanController::class, 'contracts'])->name('contracts.index');
    Route::get('/reports', [LoanController::class, 'reports'])->name('reports.index');
    Route::view('/settings', 'settings')->name('settings');
    Route::view('/profile', 'profile')->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
});
