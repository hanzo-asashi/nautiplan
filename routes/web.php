<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RenjaController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Planning: Renstra & Renja
    Route::resource('renstra', RenstraController::class);
    Route::resource('renja', RenjaController::class);

    // Programs & Activities
    Route::resource('programs', ProgramController::class);
    Route::resource('activities', ActivityController::class);
    Route::post('activities/{activity}/documents', [ActivityController::class, 'uploadDocument'])->name('activities.documents.upload');
    Route::delete('activity-documents/{document}', [ActivityController::class, 'deleteDocument'])->name('activities.documents.delete');

    // Budgets
    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('budgets', [BudgetController::class, 'storeBudget'])->name('budgets.store');
    Route::put('budgets/{budget}', [BudgetController::class, 'updateBudget'])->name('budgets.update');
    Route::delete('budgets/{budget}', [BudgetController::class, 'deleteBudget'])->name('budgets.delete');
    Route::post('budgets/realizations', [BudgetController::class, 'storeRealization'])->name('budgets.realizations.store');
    Route::post('budgets/realizations/{realization}/verify', [BudgetController::class, 'verifyRealization'])->name('budgets.realizations.verify');
    Route::delete('budgets/realizations/{realization}', [BudgetController::class, 'deleteRealization'])->name('budgets.realizations.delete');

    // Master Data: Units & Fiscal Years
    Route::resource('units', UnitController::class);
    Route::resource('fiscal-years', FiscalYearController::class);
    Route::post('fiscal-years/{fiscal_year}/toggle-active', [FiscalYearController::class, 'toggleActive'])->name('fiscal-years.toggle-active');
    Route::post('fiscal-years/{fiscal_year}/toggle-lock', [FiscalYearController::class, 'toggleLock'])->name('fiscal-years.toggle-lock');

    // User Management
    Route::resource('users', UserManagementController::class)->middleware('role:super-admin');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('role:super-admin');
});

require __DIR__.'/settings.php';
