<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Controllers\Admin\AttendanceController;

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
| Attendance Module (Bypass strict Admin Check to stop Redirects)
|--------------------------------------------------------------------------
| These routes still require login (Access Control [89]), but they won't 
| kick you out if the system doesn't recognize your "Admin" role yet.
*/

Route::middleware(['auth'])->group(function () {
    // Staff/Admin Mark Attendance
    Route::get('/admin/attendance', [AttendanceController::class, 'create'])->name('admin.attendance.create');
    
    // Save Data (Secure POST - Data Protection [138])
    Route::post('/admin/attendance/store', [AttendanceController::class, 'store'])->name('admin.attendance.store');
    
    // Admin Logs (Observer View)
    Route::get('/admin/attendance/logs', [AttendanceController::class, 'index'])->name('admin.attendance.index');
});

/*
|--------------------------------------------------------------------------
| Main Admin Group (Strictly protected by EnsureUserIsAdmin)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(EnsureUserIsAdmin::class)->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // User management (Admin only)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['show','destroy'])
        ->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);

    Route::patch('users/{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggleStatus')
        ->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);

    // Faculty, Department, Course CRUD
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
