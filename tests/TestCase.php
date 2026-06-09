<?php

namespace Akika\LaravelMpesaMultivendor\Tests;

use Akika\LaravelMpesaMultivendor\MpesaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MpesaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('mpesa.env', 'sandbox');
        $app['config']->set('mpesa.debug', false);
        $app['config']->set('mpesa.sandbox.url', 'https://sandbox.safaricom.co.ke');
        $app['config']->set('mpesa.production.url', 'https://api.safaricom.co.ke');
    }
}
