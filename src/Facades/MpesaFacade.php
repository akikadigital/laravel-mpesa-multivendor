<?php

namespace Akika\LaravelMpesaMultivendor\Facades;

use Illuminate\Support\Facades\Facade;

class MpesaFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa';
    }
}
