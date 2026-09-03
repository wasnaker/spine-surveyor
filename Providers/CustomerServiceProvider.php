<?php

declare(strict_types=1);

namespace Modules\Customer\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Customer\Listeners\LogBranchActivity;
use Modules\Customer\Listeners\LogCustomerActivity;

class CustomerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // HOOK — entity lifecycle (HasLifecycleHooks) untuk 2 entity modul.
        // Vat hook di-handle oleh spine-vat (module terpisah).
        Event::listen(\Spine\Events\EntityCreated::class, LogCustomerActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogCustomerActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogCustomerActivity::class . '@deleted');

        Event::listen(\Spine\Events\EntityCreated::class, LogBranchActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogBranchActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogBranchActivity::class . '@deleted');
    }
}
