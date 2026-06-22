<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== GUEST ROUTES ====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ==================== AUTH ROUTES ====================
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth'])->group(function () {

    // ---- DASHBOARD ----
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- TRANSACTIONS ----
    Route::prefix('transactions')->name('transactions.')->group(function () {

        // === STATIC ROUTES (harus di atas route dinamis) ===

        // Import
        Route::get('/import', [TransactionController::class, 'importForm'])->name('import');
        Route::post('/import', [TransactionController::class, 'importCsv'])->name('import.process');
        Route::get('/download-template', [TransactionController::class, 'downloadTemplate'])->name('download-template');

        //   TAMBAHKAN ROUTE INI DI DALAM GROUP transactions
        Route::get('/import/{csvImport}/detail', [TransactionController::class, 'showImportDetail'])
            ->name('import.detail');

        // Batch Update
        Route::get('/batch-cost', [TransactionController::class, 'batchCostForm'])->name('batch-cost');
        Route::post('/batch-update-costs', [TransactionController::class, 'batchUpdateCosts'])->name('batch-update-costs');

        // Weekly Edit
        Route::get('/edit-weekly', [TransactionController::class, 'editWeeklyCosts'])->name('edit-weekly');
        Route::post('/update-weekly', [TransactionController::class, 'updateWeeklyCosts'])->name('update-weekly');

        // Export & Bulk
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
        Route::delete('/bulk-delete', [TransactionController::class, 'bulkDelete'])->name('bulk-delete');

        // Delete Import
        Route::delete('/csv-imports/{csvImport}', [TransactionController::class, 'destroyImport'])
            ->name('import.destroy');

        // === DYNAMIC ROUTES (HARUS DI BAWAH ROUTE STATIS) ===
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });

    // ---- BACKWARD COMPATIBILITY ----
    Route::delete('/csv-imports/{csvImport}', [TransactionController::class, 'destroyImport'])
        ->name('csv-imports.destroy');
});
