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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

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

        RateLimiter::for('api', function (Request $request) {
            return [
                Limit::perMinute((int) env('API_RATE_LIMIT_PER_MINUTE', 120))
                    ->by($request->user()?->id ?: $request->ip()),
            ];
        });

        RateLimiter::for('auth', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute((int) env('AUTH_RATE_LIMIT_PER_MINUTE', 10))
                    ->by($email.'|'.$request->ip()),
            ];
        });

        RateLimiter::for('verification', function (Request $request) {
            return [
                Limit::perMinute(6)
                    ->by($request->user()?->id ?: $request->ip()),
            ];
        });

        RateLimiter::for('uploads', function (Request $request) {
            return [
                Limit::perMinute((int) env('UPLOAD_RATE_LIMIT_PER_MINUTE', 20))
                    ->by($request->user()?->id ?: $request->ip()),
            ];
        });

        Scramble::configure()
        ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });
    }
}
