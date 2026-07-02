<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityIndicatorController;
use App\Http\Controllers\ActivityReportController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\KpiDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RenjaController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\ReportController;
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
    Route::get('activities/{activity}/kanban', [ActivityController::class, 'kanban'])->name('activities.kanban')->middleware('throttle:60,1');
    Route::get('activities/{activity}/revisions', [ActivityController::class, 'revisions'])->name('activities.revisions')->middleware('throttle:60,1');
    Route::put('sub-activities/{subActivity}/status', [ActivityController::class, 'updateSubActivityStatus'])->name('sub-activities.update-status')->middleware('throttle:60,1');
    Route::post('activities/{activity}/documents', [ActivityController::class, 'uploadDocument'])->name('activities.documents.upload')->middleware('throttle:30,1');
    Route::delete('activity-documents/{document}', [ActivityController::class, 'deleteDocument'])->name('activities.documents.delete')->middleware('throttle:30,1');
    Route::post('activities/{activity}/indicators', [ActivityIndicatorController::class, 'store'])->name('activities.indicators.store');
    Route::put('activities/indicators/{indicator}', [ActivityIndicatorController::class, 'update'])->name('activities.indicators.update');
    Route::delete('activities/indicators/{indicator}', [ActivityIndicatorController::class, 'destroy'])->name('activities.indicators.destroy');

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

    // KPI & Monitoring
    Route::get('monitoring/kpi', [KpiDashboardController::class, 'index'])->name('monitoring.kpi');

    // Quarterly Reporting & M&E Workflow
    Route::get('monitoring/reports', [ActivityReportController::class, 'index'])->name('monitoring.reports.index');
    Route::get('monitoring/reports/{activity}/{quarter}', [ActivityReportController::class, 'show'])->name('monitoring.reports.show');
    Route::post('monitoring/reports/{activity}/{quarter}', [ActivityReportController::class, 'storeOrUpdate'])->name('monitoring.reports.store');
    Route::post('monitoring/reports/{activity}/{quarter}/evaluate', [ActivityReportController::class, 'evaluate'])->name('monitoring.reports.evaluate');

    // Approvals Workflow
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('activities/{activity}/submit-approval', [ApprovalController::class, 'submit'])->name('activities.submit-approval');
    Route::post('approvals/{approvalRequest}/action', [ApprovalController::class, 'action'])->name('approvals.action');

    // Reporting & Export Workflow
    Route::get('reports/gantt', [ReportController::class, 'gantt'])->name('reports.gantt');
    Route::get('reports/analytics', [ReportController::class, 'analytics'])->name('reports.analytics');
    Route::get('reports/calendar', [ReportController::class, 'calendar'])->name('reports.calendar');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('reports/import/template', [ReportController::class, 'downloadTemplate'])->name('reports.import.template');
    Route::post('reports/import/excel', [ReportController::class, 'importExcel'])->name('reports.import.excel');
    Route::get('reports/activity/{activity}/pdf', [ReportController::class, 'downloadPdfActivity'])->name('reports.activity.pdf');
    Route::get('reports/quarterly/{activity}/{quarter}/pdf', [ReportController::class, 'downloadPdfQuarterly'])->name('reports.quarterly.pdf');

    // Notifications Workflow
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/stream', [NotificationController::class, 'stream'])->name('notifications.stream')->middleware('throttle:30,1');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->middleware('throttle:60,1');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all')->middleware('throttle:60,1');
});

require __DIR__.'/settings.php';
