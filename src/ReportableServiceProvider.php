<?php

declare(strict_types=1);

namespace Corepine\Reportable;

use Corepine\Reportable\Console\Commands\InstallReportableCommand;
use Corepine\Reportable\Services\ReportableManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ReportableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/corepine-reportable.php', 'corepine-reportable');

        $this->app->singleton(ReportableManager::class, static fn (): ReportableManager => new ReportableManager());
        $this->app->alias(ReportableManager::class, 'reportable');
        $this->app->alias(ReportableManager::class, 'corepine-reportable');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'corepine-reportable');

        Blade::component('corepine-reportable::components.report-button', 'corepine-report-button');
        Blade::component('corepine-reportable::components.report-modal', 'corepine-report-modal');
        Blade::component('corepine-reportable::components.report-count', 'corepine-report-count');

        // Register Livewire components
        \Livewire\Livewire::component('corepine-report-modal', \Corepine\Reportable\Livewire\ReportModal::class);
        \Livewire\Livewire::component('corepine-reports-index', \Corepine\Reportable\Livewire\ReportsIndex::class);

        if ((bool) config('corepine-reportable.ui.routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallReportableCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/corepine-reportable.php' => config_path('corepine-reportable.php'),
        ], 'corepine-reportable-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'corepine-reportable-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/corepine-reportable'),
        ], 'corepine-reportable-views');
    }
}
