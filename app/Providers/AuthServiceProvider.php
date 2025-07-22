<?php


namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Supplier::class => \App\Policies\SupplierPolicy::class,
        \App\Models\ProductCBD::class => \App\Policies\ProductCBDPolicy::class,
        \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
        \App\Models\Order::class => \App\Policies\OrderPolicy::class,
        \App\Models\Cart::class => \App\Policies\CartPolicy::class,
        \App\Models\CbdArrival::class => \App\Policies\ArrivalPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        // Gate pour vérifier si l'utilisateur est administrateur
        Gate::define('admin', function ($user) {
            return $user->is_admin;
        });

        // Gate pour permettre à tout utilisateur connecté d'accéder à certaines actions
        Gate::define('authenticated', function ($user) {
            return $user !== null; // Vérifie simplement que l'utilisateur est connecté
        });

        // Gate pour permettre aux utilisateurs de voir des ressources publiques
        Gate::define('view-public-resources', function ($user) {
            return true; // Tout utilisateur connecté peut voir les ressources publiques
        });
    }
}
