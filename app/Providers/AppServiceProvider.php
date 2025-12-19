<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Claim;

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

    $this->app->bind(\App\Repositories\PositionRepositoryInterface::class, \App\Repositories\EloquentPositionRepository::class);
  
    }

    
    public function boot(): void
    {
        View::composer('layouts.staff', function ($view) {
        if (Auth::check() && Auth::user()->staff) {
            $staffId = Auth::user()->staff->staff_id;
            
            $sidebarRejectionCount = Claim::where('staff_id', $staffId)
                ->where('status', 'Rejected')
                ->where('is_seen', 0)
                ->count();

            $view->with('sidebarRejectionCount', $sidebarRejectionCount);
        }
    });

        Gate::policy(\App\Models\Payroll::class, \App\Policies\PayrollPolicy::class);
        \App\Models\Attendance::observe(\App\Observers\AttendanceObserver::class);
    }
}