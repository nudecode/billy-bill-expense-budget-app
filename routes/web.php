<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\AccountsIndex;
use App\Livewire\BillersIndex;
use App\Livewire\BillsIndex;
use App\Livewire\Dashboard;
use App\Livewire\IncomeIndex;
use App\Livewire\PaymentsIndex;
use App\Livewire\RecurringBillsIndex;
use App\Livewire\RecurringIncomeIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',        Dashboard::class)->name('dashboard');
    Route::get('/bills',            BillsIndex::class)->name('bills');
    Route::get('/recurring-bills',  RecurringBillsIndex::class)->name('recurring-bills');
    Route::get('/income',           IncomeIndex::class)->name('income');
    Route::get('/recurring-income', RecurringIncomeIndex::class)->name('recurring-income');
    Route::get('/billers',          BillersIndex::class)->name('billers');
    Route::get('/payments',         PaymentsIndex::class)->name('payments');
    Route::get('/accounts',         AccountsIndex::class)->name('accounts');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
