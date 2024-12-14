<?php

namespace App\Providers;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Models\Author' => 'App\Policies\AuthorPolicy',
        // Add other model-to-policy mappings as needed
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! app()->routesAreCached()) {
            // Passport::tokens();
        }
    }
}

