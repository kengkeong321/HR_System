<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsStaffOnly;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PayrollSettingController;
use App\Http\Controllers\Admin\ClaimController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\Staff\StaffClaimController;
use App\Models\Attendance;
use Illuminate\Http\Request;

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
    Route::get('/admin/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('admin.attendance.edit');
    Route::patch('/admin/attendance/{id}/update', [AttendanceController::class, 'update'])->name('admin.attendance.update');
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::get('/admin/leave', [LeaveController::class, 'adminIndex'])->name('leave.index');

    // Use PATCH for state updates to comply with Data Protection [138]
    Route::patch('/admin/leave/{id}/update', [LeaveController::class, 'adminUpdate'])->name('leave.update');

        // --- Staff Payslip View ---
    Route::get('/staff/my-payslips', [PayslipController::class, 'myHistory'])->name('staff.payroll.my_payslips');

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
        ->except(['show', 'destroy'])
        ->middleware(\App\Http\Middleware\EnsureUserIsAdmin::class);

    Route::patch('users/{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggleStatus')
        ->middleware(\App\Http\Middleware\EnsureUserIsAdmin::class);

    /*
    |--------------------------------------------------------------------------
    | Payroll Management (Admin Only)
    |--------------------------------------------------------------------------
    */
    Route::post('payroll/generate', [PayrollController::class, 'generateBatch'])->name('payroll.generateBatch');

    Route::get('payroll/batch/{id}', [PayrollController::class, 'show'])->name('payroll.batch_view');

    Route::post('payroll/batch/{id}/approve-l1', [PayrollController::class, 'approveL1'])->name('payroll.approve_l1');
    Route::post('payroll/batch/{id}/approve-l2', [PayrollController::class, 'approveL2'])->name('payroll.approve_l2');

    Route::post('payroll/batch/{id}/reject', [PayrollController::class, 'reject'])->name('payroll.reject');
    Route::get('payroll/batch/{id}/export', [PayrollController::class, 'exportReport'])->name('payroll.export');

    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/settings', [PayrollSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/update', [PayrollSettingController::class, 'update'])->name('settings.update');
    });
    Route::resource('payroll', PayrollController::class)->except(['show', 'create', 'store']);


    /*
    |--------------------------------------------------------------------------
    | Allowance (Admin only)
    |--------------------------------------------------------------------------
    */
    Route::prefix('claims')->name('claims.')->group(function () {

        Route::get('/', [ClaimController::class, 'index'])->name('index');

        Route::post('/{id}/approve', [ClaimController::class, 'approve'])->name('approve');

        Route::post('/{id}/reject', [ClaimController::class, 'reject'])->name('reject');
    });


    // --- Faculty, Department, Course CRUD ---
    Route::resource('faculties', \App\Http\Controllers\Admin\FacultyController::class)->except(['show', 'destroy']);
    // Positions module
    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class)->except(['show', 'destroy']);
    Route::patch('positions/{position}/status', [\App\Http\Controllers\Admin\PositionController::class, 'toggleStatus'])->name('positions.toggleStatus');
    Route::post('positions/page', [\App\Http\Controllers\Admin\PositionController::class, 'page'])->name('positions.page');

    // AJAX: return active departments for a faculty (used by faculties index modal)
    Route::get('faculties/{faculty}/departments', [\App\Http\Controllers\Admin\FacultyController::class, 'departments'])->name('faculties.departments');
    Route::patch('faculties/{faculty}/status', [\App\Http\Controllers\Admin\FacultyController::class, 'toggleStatus'])->name('faculties.toggleStatus');

    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['show', 'destroy']);
    // Assignment routes for departments (assign courses)
    Route::get('departments/{department}/assign', [\App\Http\Controllers\Admin\DepartmentController::class, 'assign'])->name('departments.assign');
    Route::post('departments/{department}/assign', [\App\Http\Controllers\Admin\DepartmentController::class, 'assignStore'])->name('departments.assign.store');
    // API for modal: get assigned courses for a department (used by the assignments modal)
    Route::get('departments/{department}/assignments', [\App\Http\Controllers\Admin\DepartmentController::class, 'assignments'])->name('departments.assignments');
    Route::patch('departments/{department}/status', [\App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('departments.toggleStatus');

    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->except(['show', 'destroy']);
    Route::patch('courses/{course}/status', [\App\Http\Controllers\Admin\CourseController::class, 'toggleStatus'])->name('courses.toggleStatus');

    // AJAX pagination endpoints
    Route::post('faculties/page', [\App\Http\Controllers\Admin\FacultyController::class, 'page'])->name('faculties.page');
    Route::post('departments/page', [\App\Http\Controllers\Admin\DepartmentController::class, 'page'])->name('departments.page');
    Route::post('courses/page', [\App\Http\Controllers\Admin\CourseController::class, 'page'])->name('courses.page');
    Route::post('users/page', [\App\Http\Controllers\Admin\UserController::class, 'page'])->name('users.page')->middleware(\App\Http\Middleware\EnsureUserIsAdmin::class);


});

// Positions API (Active only) - returns active positions list for dropdowns
Route::get('/api/positions', [\App\Http\Controllers\Api\PositionController::class, 'index'])->name('api.positions.index');

// Staff routes
Route::prefix('staff')->name('staff.')->middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('staff.dashboard');
    })->name('dashboard');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'staffCreate'])->name('attendance.create');
    Route::post('/attendance/store', [AttendanceController::class, 'staffStore'])->name('attendance.store');

    // payslip
    Route::get('/staff/my-payslips', [PayslipController::class, 'myHistory'])->name('staff.payroll.my_payslips');
    Route::get('/payroll/{id}/export', [PayrollController::class, 'exportSlip'])->name('payroll.export');

    // leave
    Route::get('/leave', [LeaveController::class, 'staffIndex'])->name('leave.index');
    Route::post('/leave/store', [LeaveController::class, 'store'])->name('leave.store');

    // claims
    Route::get('/claims/create', [StaffClaimController::class, 'create'])->name('claims.create');
Route::post('/claims/store', [StaffClaimController::class, 'store'])->name('claims.store');

    Route::get('/claims/history', [StaffClaimController::class, 'index'])->name('claims.index');
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




/*
|--------------------------------------------------------------------------
| training
|--------------------------------------------------------------------------
*/


use App\Http\Controllers\Admin\TrainingController;

Route::middleware(['auth'])->group(function () {
    
    //(Static Routes) ---
    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
    Route::get('/training/create/new', [TrainingController::class, 'create'])->name('training.create');
    
    
    Route::get('/training/records', [TrainingController::class, 'records'])->name('training.records');
    Route::post('/training/records', [TrainingController::class, 'records']);

    //(Dynamic Routes) ---
    Route::get('/training/{id}', [TrainingController::class, 'show'])->name('training.show');
    Route::get('/training/{id}/edit', [TrainingController::class, 'edit'])->name('training.edit');
    Route::get('/training/{id}/assign', [App\Http\Controllers\Admin\TrainingController::class, 'assignPage'])->name('training.assignPage');
    
    // (Actions) ---
    Route::post('/training', [TrainingController::class, 'store'])->name('training.store');
    Route::put('/training/{id}', [TrainingController::class, 'update'])->name('training.update');
    Route::delete('/training/{id}', [TrainingController::class, 'destroy'])->name('training.destroy');
    Route::post('/training/{id}/assign', [App\Http\Controllers\Admin\TrainingController::class, 'assign'])->name('training.assign');
    Route::post('/training/{id}/feedback', [TrainingController::class, 'storeFeedback'])->name('training.feedback');
    Route::post('/training/{id}/status/{userId}', [TrainingController::class, 'updateStatus'])->name('training.updateStatus');
    Route::delete('/training/{id}/detach/{userId}', [TrainingController::class, 'detachParticipant'])->name('training.detach');
    Route::post('/training/{id}/status-toggle', [TrainingController::class, 'activate'])->name('training.status.toggle');
});


/*
|--------------------------------------------------------------------------
| stafftraining
|--------------------------------------------------------------------------
*/



use App\Http\Controllers\Staff\StaffTrainingController;

Route::middleware(['auth'])->group(function () {
   
    Route::get('/staff/my-trainings', [StaffTrainingController::class, 'index'])
         ->name('staff.trainings.index');

Route::post('/staff/feedback/store', [StaffTrainingController::class, 'storeFeedback'])
         ->name('staff.feedback.store');

         
});


/*
|--------------------------------------------------------------------------
| trainingAPI
|--------------------------------------------------------------------------
*/


Route::get('/my-trainings', [\App\Http\Controllers\Admin\TrainingController::class, 'myApiExport']);











Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {

    Route::get('/admin/attendance/test-api', function () {
        return view('admin.attendance.api_test');
    })->name('admin.attendance.test_api');
});

/*
|--------------------------------------------------------------------------
| staff management
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\StaffController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. AJAX Check (Simplified name)
    // The actual name will be 'admin.staff.checkEmail'
    Route::get('staff/check-email', [StaffController::class, 'checkEmail'])->name('staff.checkEmail');

    // 2. Pagination route
    Route::get('staff/page', [StaffController::class, 'page'])->name('staff.page');

    // 3. Resource routes (index, create, store, etc.)
    Route::resource('staff', StaffController::class);
});
