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
            // Return a new instance of the Mpesa class with the configuration values
            return new Mpesa(
                config('mpesa.shortcode', ''),
                config('mpesa.consumer_key', ''),
                config('mpesa.consumer_secret', ''),
                config('mpesa.api_username', ''),
                config('mpesa.api_password', ''),
                config('mpesa.passkey'),
            );
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
            ], 'mpesa-config');

            // Register InstallAkikaMpesaMultivendorPackage command
            $this->commands([
                Commands\InstallAkikaMpesaMultivendorPackage::class
            ]);
        }
    }
}
