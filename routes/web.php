<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\PayrollController; 

/*
|--------------------------------------------------------------------------
| Public & Auth Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Staff & System Routes
|--------------------------------------------------------------------------
| Routes that require login but are accessible to both Admin and Staff.
*/

Route::middleware(['auth'])->group(function () {
    
    // --- Attendance Module ---
    Route::get('/admin/attendance', [AttendanceController::class, 'create'])->name('admin.attendance.create');
    Route::post('/admin/attendance/store', [AttendanceController::class, 'store'])->name('admin.attendance.store');
    Route::get('/admin/attendance/logs', [AttendanceController::class, 'index'])->name('admin.attendance.index');

    // --- Staff Payslip View ---
    // Moved here so non-admin staff can access their own history
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('my.payslips');
    
    // Individual PDF Download (Reuse Admin Controller Export)
    Route::get('/payroll/export-slip/{id}', [PayrollController::class, 'exportSlip'])->name('admin.payroll.export_slip');
});

/*
|--------------------------------------------------------------------------
| Main Admin Group (Strictly protected by EnsureUserIsAdmin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(EnsureUserIsAdmin::class)->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // --- User Management (Admin only) ---
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['show','destroy'])
        ->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);

    Route::patch('users/{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggleStatus')
        ->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);

    /*
    |--------------------------------------------------------------------------
    | Payroll Management (Admin Only)
    |--------------------------------------------------------------------------
    */
    // 1. Generate Batch
    Route::post('payroll/generate', [PayrollController::class, 'generateBatch'])->name('payroll.generateBatch');
    
    // 2. View Batch (The 'batch_view' route used in your dashboard)
    Route::get('payroll/batch/{id}', [PayrollController::class, 'show'])->name('payroll.batch_view');

    // 3. Approvals
    Route::post('payroll/batch/{id}/approve-l1', [PayrollController::class, 'approveL1'])->name('payroll.approve_l1');
    Route::post('payroll/batch/{id}/approve-l2', [PayrollController::class, 'approveL2'])->name('payroll.approve_l2');

    // 4. Batch Export (Full Report)
    Route::get('payroll/batch/{id}/export', [PayrollController::class, 'exportReport'])->name('payroll.export');

    // 5. Payroll Resource (Handles Index, Edit, Update)
    Route::resource('payroll', PayrollController::class)->except(['show', 'create', 'store']);


    // --- Faculty, Department, Course CRUD ---
    Route::resource('faculties', \App\Http\Controllers\Admin\FacultyController::class)->except(['show','destroy']);
    Route::patch('faculties/{faculty}/status', [\App\Http\Controllers\Admin\FacultyController::class, 'toggleStatus'])->name('faculties.toggleStatus');

    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['show','destroy']);
    Route::patch('departments/{department}/status', [\App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('departments.toggleStatus');

    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->except(['show','destroy']);
    Route::patch('courses/{course}/status', [\App\Http\Controllers\Admin\CourseController::class, 'toggleStatus'])->name('courses.toggleStatus');

    // AJAX pagination endpoints
    Route::post('faculties/page', [\App\Http\Controllers\Admin\FacultyController::class, 'page'])->name('faculties.page');
    Route::post('departments/page', [\App\Http\Controllers\Admin\DepartmentController::class, 'page'])->name('departments.page');
    Route::post('courses/page', [\App\Http\Controllers\Admin\CourseController::class, 'page'])->name('courses.page');
    Route::post('users/page', [\App\Http\Controllers\Admin\UserController::class, 'page'])->name('users.page')->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);
});

/*
|--------------------------------------------------------------------------
| System Utilities
|--------------------------------------------------------------------------
*/

Route::post('/_sidebar/toggle', function (\Illuminate\Http\Request $request) {
    session(['sidebar_collapsed' => (bool) $request->input('collapsed')]);
    return response()->json(['ok' => true]);
});