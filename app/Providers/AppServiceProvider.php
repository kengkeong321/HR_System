<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
   
    public function register(): void
    {
        // Bind repository interfaces to Eloquent implementations
        $this->app->bind(\App\Repositories\FacultyRepositoryInterface::class, \App\Repositories\EloquentFacultyRepository::class);
        $this->app->bind(\App\Repositories\DepartmentRepositoryInterface::class, \App\Repositories\EloquentDepartmentRepository::class);
        $this->app->bind(\App\Repositories\CourseRepositoryInterface::class, \App\Repositories\EloquentCourseRepository::class);


    $this->app->bind('training_service', function ($app) {
        return new \App\Services\TrainingService();
    });

    $this->app->bind('staff_training_engine', function ($app) {
        return new \App\Services\StaffTrainingService();
    });

  
    }

    
    public function boot(): void
    {
        View::composer('*', function ($view) {

            if (Auth::check()) {


                $user = Auth::user();

                if ($user->staff) {
                    $count = \App\Models\Claim::where('staff_id', $user->staff->staff_id)
                        ->where('status', 'Rejected')
                        ->count();

                    $view->with('sidebarRejectionCount', $count);
                }
            }
        });

        Gate::policy(\App\Models\Payroll::class, \App\Policies\PayrollPolicy::class);
        \App\Models\Attendance::observe(\App\Observers\AttendanceObserver::class);
    }
}
