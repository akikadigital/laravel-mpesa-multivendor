<?php

use Akika\LaravelMpesaMultivendor\Facades\MpesaFacade;

uses()->group('facades');

it('uses mpesa as facade accessor', function () {
    $reflection = new ReflectionClass(MpesaFacade::class);

    $method = $reflection->getMethod('getFacadeAccessor');
    $method->setAccessible(true);

    expect($method->invoke(null))
        ->toBe('mpesa');
});
