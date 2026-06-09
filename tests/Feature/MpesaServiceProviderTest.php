<?php

use Akika\LaravelMpesaMultivendor\Commands\InstallAkikaMpesaMultivendorPackage;
use Akika\LaravelMpesaMultivendor\Facades\MpesaFacade;
use Akika\LaravelMpesaMultivendor\Mpesa;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Artisan;

uses()->group('service-provider');

beforeEach(function () {
    config()->set('mpesa.env', 'sandbox');
    config()->set('mpesa.debug', false);
    config()->set('mpesa.sandbox.url', 'https://sandbox.safaricom.co.ke');

    config()->set('mpesa.shortcode', '600000');
    config()->set('mpesa.consumer_key', 'consumer-key');
    config()->set('mpesa.consumer_secret', 'consumer-secret');
    config()->set('mpesa.api_username', 'api-user');
    config()->set('mpesa.api_password', 'api-password');
    config()->set('mpesa.passkey', 'test-passkey');
});

it('merges package configuration', function () {
    expect(config('mpesa'))
        ->toBeArray()
        ->not->toBeEmpty();
});

it('registers mpesa singleton in the container', function () {
    expect(app('mpesa'))->toBeInstanceOf(Mpesa::class);
});

it('resolves the same mpesa singleton instance', function () {
    $first = app('mpesa');
    $second = app('mpesa');

    expect($second)->toBe($first);
});

it('resolves mpesa facade root from container', function () {
    expect(MpesaFacade::getFacadeRoot())
        ->toBe(app('mpesa'));
});

it('registers mpesa alias', function () {
    $aliases = AliasLoader::getInstance()->getAliases();

    expect($aliases)
        ->toHaveKey('Mpesa')
        ->and($aliases['Mpesa'])
        ->toBe(MpesaFacade::class);
});

it('registers install command', function () {
    expect(app(InstallAkikaMpesaMultivendorPackage::class))
        ->toBeInstanceOf(InstallAkikaMpesaMultivendorPackage::class);
});

it('makes install command available to artisan', function () {
    $commands = Artisan::all();

    expect($commands)
        ->toHaveKey('mpesa-multivendor:install');
});
