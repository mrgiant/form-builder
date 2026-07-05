<?php

namespace Mrgiant\FormBuilder;

use Illuminate\Support\ServiceProvider;
use Mrgiant\FormBuilder\Console\InstallCommand;
use Mrgiant\FormBuilder\Console\UninstallCommand;

class FormBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/form-builder.php', 'form-builder');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'form-builder');

        // Also register the bundled views under the default (un-namespaced)
        // paths as a fallback, so controllers that reference bare view names
        // like 'admin.forms.index' resolve to the package's Blade files. A host
        // view of the same name still wins (default paths are searched first).
        $this->callAfterResolving('view', function ($view) {
            $view->addLocation(__DIR__.'/../resources/views');
        });

        if (config('form-builder.register_routes')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();

            $this->commands([
                InstallCommand::class,
                UninstallCommand::class,
            ]);
        }
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/form-builder.php' => config_path('form-builder.php'),
        ], 'form-builder-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'form-builder-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/form-builder'),
        ], 'form-builder-views');

        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/form-builder'),
        ], 'form-builder-assets');
    }
}
