<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- ADD THIS IMPORT

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            \App\Repositories\Core\Contracts\BaseRepositoryInterface::class,
            \App\Repositories\Core\Eloquent\BaseRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\RoleRepositoryContract::class,
            \App\Repositories\RoleRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\EventRepositoryContract::class,
            \App\Repositories\EventRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TicketTypeRepositoryContract::class,
            \App\Repositories\TicketTypeRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TicketRepositoryContract::class,
            \App\Repositories\TicketRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\GateRepositoryContract::class,
            \App\Repositories\GateRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TicketScanLogRepositoryContract::class,
            \App\Repositories\TicketScanLogRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\EventCounterRepositoryContract::class,
            \App\Repositories\EventCounterRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryContract::class,
            \App\Repositories\UserRepository::class
        );

        // Service bindings
        $this->app->bind(
            \App\Services\Core\Contracts\BaseServiceInterface::class,
            \App\Services\Core\Eloquent\BaseService::class
        );

        $this->app->bind(
            \App\Services\Contracts\RoleServiceContract::class,
            \App\Services\RoleService::class
        );

        $this->app->bind(
            \App\Services\Contracts\EventServiceContract::class,
            \App\Services\EventService::class
        );

        $this->app->bind(
            \App\Services\Contracts\TicketTypeServiceContract::class,
            \App\Services\TicketTypeService::class
        );

        $this->app->bind(
            \App\Services\Contracts\TicketServiceContract::class,
            \App\Services\TicketService::class
        );

        $this->app->bind(
            \App\Services\Contracts\GateServiceContract::class,
            \App\Services\GateService::class
        );

        $this->app->bind(
            \App\Services\Contracts\TicketScanLogServiceContract::class,
            \App\Services\TicketScanLogService::class
        );

        $this->app->bind(
            \App\Services\Contracts\ScanServiceContract::class,
            \App\Services\ScanService::class
        );

        $this->app->bind(
            \App\Services\Contracts\UserServiceContract::class,
            \App\Services\UserService::class
        );

        $this->app->bind(
            \App\Services\Contracts\PaymentServiceContract::class,
            \App\Services\PaymentService::class
        );

        $this->app->bind(
            \App\Services\Contracts\NotificationServiceContract::class,
            \App\Services\NotificationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Rate limiter for scan request endpoint
        // 60 requests per minute per IP address
        RateLimiter::for('scan-request', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Rate limiter for scan confirm endpoint
        // 30 requests per minute per authenticated user
        RateLimiter::for('scan-confirm', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        // General API rate limiter (can be used for other endpoints)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });
    }
}
