<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces to Eloquent implementations
        $this->app->bind(\App\Repositories\FacultyRepositoryInterface::class, \App\Repositories\EloquentFacultyRepository::class);
        $this->app->bind(\App\Repositories\DepartmentRepositoryInterface::class, \App\Repositories\EloquentDepartmentRepository::class);
        $this->app->bind(\App\Repositories\CourseRepositoryInterface::class, \App\Repositories\EloquentCourseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
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
    }
}
