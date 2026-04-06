<?php

namespace App\Providers;

use App\Models\Coord;
use App\Models\Polygon;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Policies\CoordPolicy;
use App\Policies\PolygonPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\Rbac;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Polygon::class, PolygonPolicy::class);
        Gate::policy(Coord::class, CoordPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Gate::before(function (?User $user, string $ability) {
            if (Rbac::isAdmin($user)) {
                return true;
            }

            return null;
        });

        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });
    }
}
