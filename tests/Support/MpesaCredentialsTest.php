<?php

use Akika\LaravelMpesaMultivendor\Support\MpesaCredentials;

uses()->group('credentials');

it('creates credentials from array', function () {
    $credentials = MpesaCredentials::fromArray([
        'shortcode' => '600000',
        'consumer_key' => 'key',
        'consumer_secret' => 'secret',
        'api_username' => 'api-user',
        'api_password' => 'api-password',
        'passkey' => 'passkey',
    ]);

    expect($credentials->shortcode)->toBe('600000')
        ->and($credentials->consumerKey)->toBe('key')
        ->and($credentials->consumerSecret)->toBe('secret')
        ->and($credentials->apiUsername)->toBe('api-user')
        ->and($credentials->apiPassword)->toBe('api-password')
        ->and($credentials->passkey)->toBe('passkey');
});

it('uses defaults when array values are missing', function () {
    $credentials = MpesaCredentials::fromArray([]);

    expect($credentials->shortcode)->toBe('')
        ->and($credentials->consumerKey)->toBe('')
        ->and($credentials->consumerSecret)->toBe('')
        ->and($credentials->apiUsername)->toBe('')
        ->and($credentials->apiPassword)->toBe('')
        ->and($credentials->passkey)->toBeNull();
});

it('creates credentials from config', function () {
    config()->set('mpesa.shortcode', '600000');
    config()->set('mpesa.consumer_key', 'key');
    config()->set('mpesa.consumer_secret', 'secret');
    config()->set('mpesa.api_username', 'api-user');
    config()->set('mpesa.api_password', 'api-password');
    config()->set('mpesa.passkey', 'passkey');

    $credentials = MpesaCredentials::fromConfig();

    expect($credentials->shortcode)->toBe('600000')
        ->and($credentials->consumerKey)->toBe('key')
        ->and($credentials->consumerSecret)->toBe('secret')
        ->and($credentials->apiUsername)->toBe('api-user')
        ->and($credentials->apiPassword)->toBe('api-password')
        ->and($credentials->passkey)->toBe('passkey');
});

it('uses defaults when config values are missing', function () {
    config()->set('mpesa.shortcode', null);
    config()->set('mpesa.consumer_key', null);
    config()->set('mpesa.consumer_secret', null);
    config()->set('mpesa.api_username', null);
    config()->set('mpesa.api_password', null);
    config()->set('mpesa.passkey', null);

    $credentials = MpesaCredentials::fromConfig();

    expect($credentials->shortcode)->toBe('')
        ->and($credentials->consumerKey)->toBe('')
        ->and($credentials->consumerSecret)->toBe('')
        ->and($credentials->apiUsername)->toBe('')
        ->and($credentials->apiPassword)->toBe('')
        ->and($credentials->passkey)->toBeNull();
});
