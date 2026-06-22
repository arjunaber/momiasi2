<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transactions/import', [TransactionController::class, 'importForm'])->name('transactions.import');
    Route::post('/transactions/import', [TransactionController::class, 'importCsv'])->name('transactions.import.post');
    Route::get('/transactions/template', [TransactionController::class, 'downloadTemplate'])->name('transactions.template');
    Route::delete('/csv-imports/{csvImport}', [TransactionController::class, 'destroyImport'])->name('csv-imports.destroy');

    Route::resource('transactions', TransactionController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
});
