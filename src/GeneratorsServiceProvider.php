<?php

namespace Smiarowski\Generators;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerGenerator();
    }

    /**
     * Register the make:migration generator.
     */
    private function registerGenerator()
    {
        $this->app->singleton('command.smiarowski.make', function ($app) {
            return $app['Smiarowski\Generators\Commands\ApiResourceMakeCommand'];
        });

        $this->commands('command.smiarowski.make');
    }

}
