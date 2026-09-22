<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UserSession;
use App\Policies\SessionPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Model::shouldBeStrict(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Event::listen(Registered::class, SendEmailVerificationNotification::class);

        Gate::policy(User::class, StudentPolicy::class);
        Gate::policy(UserSession::class, SessionPolicy::class);
    }
}
