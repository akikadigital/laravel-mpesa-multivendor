<?php

namespace Akika\LaravelMpesaMultivendor\Facades;

use Illuminate\Support\Facades\Facade;

class Mpesa extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-mpesa';
    }
}
