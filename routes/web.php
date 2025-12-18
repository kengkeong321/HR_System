<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureUserIsAdmin;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(EnsureUserIsAdmin::class)->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Admin-only routes for user management (delete disabled)
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
        ->except(['show','destroy'])
        ->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);

    // Toggle status for users (Admin only)
    Route::patch('users/{user}/status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggleStatus')
        ->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);

    // Faculty, Department, Course CRUD accessible to Admin and Staff (delete disabled)
    Route::resource('faculties', \App\Http\Controllers\Admin\FacultyController::class)->except(['show','destroy']);
    Route::patch('faculties/{faculty}/status', [\App\Http\Controllers\Admin\FacultyController::class, 'toggleStatus'])->name('faculties.toggleStatus');

    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['show','destroy']);
    Route::patch('departments/{department}/status', [\App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('departments.toggleStatus');

    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class)->except(['show','destroy']);
    Route::patch('courses/{course}/status', [\App\Http\Controllers\Admin\CourseController::class, 'toggleStatus'])->name('courses.toggleStatus');

    // AJAX pagination endpoints (page changes handled via POST to avoid page query in URL)
    Route::post('faculties/page', [\App\Http\Controllers\Admin\FacultyController::class, 'page'])->name('faculties.page');
    Route::post('departments/page', [\App\Http\Controllers\Admin\DepartmentController::class, 'page'])->name('departments.page');
    Route::post('courses/page', [\App\Http\Controllers\Admin\CourseController::class, 'page'])->name('courses.page');
    Route::post('users/page', [\App\Http\Controllers\Admin\UserController::class, 'page'])->name('users.page')->middleware(\App\Http\Middleware\EnsureUserIsAdminOnly::class);
});

// Toggle sidebar collapse preference (used by client-side JS)
Route::post('/_sidebar/toggle', function (\Illuminate\Http\Request $request) {
    session(['sidebar_collapsed' => (bool) $request->input('collapsed')]);
    return response()->json(['ok' => true]);
});






// training

use App\Http\Controllers\Admin\TrainingController;

Route::middleware([\App\Http\Middleware\EnsureUserLoggedIn::class])->group(function () {
    
    
    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
    Route::get('/training/{id}', [TrainingController::class, 'show'])->name('training.show');
    Route::post('/training/{id}/feedback', [TrainingController::class, 'storeFeedback'])->name('training.feedback');
    Route::get('/training/create/new', [TrainingController::class, 'create'])->name('training.create');
    Route::post('/training', [TrainingController::class, 'store'])->name('training.store');
    Route::delete('/training/{id}', [TrainingController::class, 'destroy'])->name('training.destroy');
    Route::get('/training/{id}/assign', [App\Http\Controllers\Admin\TrainingController::class, 'assignPage'])->name('training.assignPage');
    Route::post('/training/{id}/assign', [App\Http\Controllers\Admin\TrainingController::class, 'assign'])->name('training.assign');
    Route::get('/training/{id}/edit', [TrainingController::class, 'edit'])->name('training.edit');
    Route::put('/training/{id}', [TrainingController::class, 'update'])->name('training.update');
    Route::match(['get', 'post'], '/training/records', [App\Http\Controllers\Admin\TrainingController::class, 'records'])->name('training.records');
    Route::post('/training/{id}/user/{userId}/status', [TrainingController::class, 'updateStatus'])->name('training.status');
    Route::delete('/training/{id}/detach/{userId}', [TrainingController::class, 'detachParticipant'])->name('training.detach');
});