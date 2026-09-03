<?php

declare(strict_types=1);

namespace Modules\Surveyor\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Surveyor\Listeners\LogSurveyorActivity;

class SurveyorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // HOOK — entity lifecycle (HasLifecycleHooks).
        // Branch sekarang adalah row di tabel surveyors dengan type='branch',
        // jadi tidak perlu listener terpisah untuk Branch.
        Event::listen(\Spine\Events\EntityCreated::class, LogSurveyorActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogSurveyorActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogSurveyorActivity::class . '@deleted');
    }
}
