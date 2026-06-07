<?php

namespace Akika\LaravelMpesaMultivendor;

use Akika\LaravelMpesaMultivendor\Facades\MpesaFacade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/mpesa.php',
            'mpesa'
        );

        $this->app->singleton('mpesa', function () {
            return Mpesa::default();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        AliasLoader::getInstance()->alias(
            'Mpesa',
            MpesaFacade::class
        );

        // Load package migrations
        if ($this->app->runningInConsole()) {

            // Publish the mpesa config file
            $this->publishes([
                __DIR__ . '/../config/mpesa.php' => config_path('mpesa.php')
            ], 'mpesa-multivendor-config');

            // Register InstallAkikaMpesaMultivendorPackage command
            $this->commands([
                Commands\InstallAkikaMpesaMultivendorPackage::class
            ]);
        }
    }
}
