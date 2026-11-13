<?php
 
namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::guessPolicyNamesUsing(function (string $class) {
            $user = request()->user();
            $folder = $user instanceof \App\Models\User ? 'BackOffice' : 'Student';
            return Str::replace('App\\Models', 'App\\Policies\\' . $folder, $class) . 'Policy';
        });
    }
}
