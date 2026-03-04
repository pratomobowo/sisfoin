<?php

namespace App\Providers;

use App\View\Composers\SuperadminComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Employee;
use App\Models\Dosen;
use App\Observers\EmployeeObserver;
use App\Observers\DosenObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super-admin bypass: Give super-admin role access to all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // Register view composers
        View::composer('superadmin.*', SuperadminComposer::class);

        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');

        // Register core observers
        Employee::observe(EmployeeObserver::class);
        Dosen::observe(DosenObserver::class);
    }
}
