<?php
namespace App\Providers;

use App\Models\Ticket;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Ticket::class => TicketPolicy::class,
    ];

    public function boot(): void
    {
    
        $this->registerPolicies();

        
        Gate::before(function (User $user, $ability) {
            return $user->role === 'admin' ? true : null;
        });
    }
}