<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::policy(\App\Models\Payroll::class, \App\Policies\PayrollPolicy::class);
    }

    protected $policies = [
    \App\Models\Payroll::class => \App\Policies\PayrollPolicy::class,
];
}
