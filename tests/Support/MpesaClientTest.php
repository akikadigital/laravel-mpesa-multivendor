<?php

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;
use Akika\LaravelMpesaMultivendor\Support\MpesaCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

uses()->group('support', 'mpesa-client');

beforeEach(function () {
    config()->set('mpesa.env', 'sandbox');
    config()->set('mpesa.debug', false);
    config()->set('mpesa.sandbox.url', 'https://sandbox.safaricom.co.ke');
    config()->set('mpesa.production.url', 'https://api.safaricom.co.ke');

    Cache::flush();
});

function mpesaCredentials(array $overrides = []): MpesaCredentials
{
    return new MpesaCredentials(
        shortcode: $overrides['shortcode'] ?? '600000',
        consumerKey: $overrides['consumerKey'] ?? 'consumer-key',
        consumerSecret: $overrides['consumerSecret'] ?? 'consumer-secret',
        apiUsername: $overrides['apiUsername'] ?? 'api-user',
        apiPassword: $overrides['apiPassword'] ?? 'api-password',
        passkey: array_key_exists('passkey', $overrides)
            ? $overrides['passkey']
            : 'test-passkey',
    );
}

function mpesaClient(array $overrides = []): MpesaClient
{
    return new MpesaClient(mpesaCredentials($overrides));
}

it('returns credentials instance', function () {
    $credentials = mpesaCredentials();

    $client = new MpesaClient($credentials);

    expect($client->credentials())->toBe($credentials);
});

it('returns sandbox base url from config', function () {
    $client = mpesaClient();

    expect($client->baseUrl())->toBe('https://sandbox.safaricom.co.ke');
});

it('returns production base url from config', function () {
    config()->set('mpesa.env', 'production');

    $client = mpesaClient();

    expect($client->baseUrl())->toBe('https://api.safaricom.co.ke');
});

it('returns debug mode value from config', function () {
    config()->set('mpesa.debug', true);

    $client = mpesaClient();

    expect($client->isDebugMode())->toBeTrue();
});

it('returns shortcode api username and passkey', function () {
    $client = mpesaClient();

    expect($client->shortcode())->toBe('600000')
        ->and($client->apiUsername())->toBe('api-user')
        ->and($client->passkey())->toBe('test-passkey');
});

it('generates stk password', function () {
    $client = mpesaClient();

    $timestamp = '20260101120000';

    $password = $client->generatePassword($timestamp);

    expect($password)->toBe(
        base64_encode('600000' . 'test-passkey' . $timestamp)
    );
});

it('throws exception when unable to encrypt api password', function () {
    File::shouldReceive('get')
        ->once()
        ->andReturn('invalid-public-key');

    $client = mpesaClient([
        'apiPassword' => 'api-password',
    ]);

    $client->getSecurityCredential();
})->throws(RuntimeException::class, 'Unable to encrypt M-Pesa initiator password.');

it('throws exception when generating password without passkey', function () {
    $client = mpesaClient([
        'passkey' => null,
    ]);

    $client->generatePassword('20260101120000');
})->throws(InvalidArgumentException::class, 'M-Pesa passkey is required.');

it('fetches access token from daraja', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'test-access-token',
        ], 200),
    ]);

    $client = mpesaClient();

    expect($client->getAccessToken())->toBe('test-access-token');

    Http::assertSentCount(1);
});

it('caches access token for subsequent calls', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'cached-access-token',
        ], 200),
    ]);

    $client = mpesaClient();

    expect($client->getAccessToken())->toBe('cached-access-token')
        ->and($client->getAccessToken())->toBe('cached-access-token');

    Http::assertSentCount(1);
});

it('throws exception when consumer credentials are missing while fetching token', function () {
    $client = mpesaClient([
        'consumerKey' => '',
        'consumerSecret' => '',
    ]);

    $client->getAccessToken();
})->throws(InvalidArgumentException::class, 'M-Pesa consumer key and secret are required.');

it('throws exception when token request fails', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'errorMessage' => 'Invalid credentials',
        ], 401),
    ]);

    $client = mpesaClient();

    $client->getAccessToken();
})->throws(RuntimeException::class, 'Failed to fetch M-Pesa access token');

it('throws exception when token response has no access token', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([], 200),
    ]);

    $client = mpesaClient();

    $client->getAccessToken();
})->throws(RuntimeException::class, 'M-Pesa access token was not returned.');

it('makes authenticated post request', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'request-token',
        ], 200),

        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint' => Http::response([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success',
        ], 200),
    ]);

    $client = mpesaClient();

    $response = $client->makeRequest(
        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint',
        ['Amount' => 100]
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
    ]);

    Http::assertSentCount(2);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://sandbox.safaricom.co.ke/mpesa/test-endpoint'
            && $request->hasHeader('Authorization', 'Bearer request-token')
            && $request['Amount'] === 100;
    });
});

it('returns empty array when daraja post response has no json body', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'request-token',
        ], 200),

        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint' => Http::response(null, 200),
    ]);

    $client = mpesaClient();

    $response = $client->makeRequest(
        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint',
        ['Amount' => 100]
    );

    expect($response)->toBe([]);
});

it('throws runtime exception when daraja post request fails', function () {
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
            'access_token' => 'request-token',
        ], 200),

        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint' => Http::response([
            'errorMessage' => 'Failed',
        ], 500),
    ]);

    $client = mpesaClient();

    $client->makeRequest(
        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint',
        ['Amount' => 100]
    );
})->throws(RuntimeException::class, 'Daraja request failed with status 500');

it('throws exception when making request without consumer credentials', function () {
    $client = mpesaClient([
        'consumerKey' => '',
        'consumerSecret' => '',
    ]);

    $client->makeRequest(
        'https://sandbox.safaricom.co.ke/mpesa/test-endpoint',
        ['Amount' => 100]
    );
})->throws(InvalidArgumentException::class, 'M-Pesa consumer key and secret are required.');

it('generates sandbox security credential successfully', function () {
    config()->set('mpesa.env', 'sandbox');

    $client = mpesaClient([
        'apiPassword' => 'api-password',
    ]);

    $credential = $client->getSecurityCredential();

    expect($credential)
        ->toBeString()
        ->not->toBeEmpty();

    expect(base64_decode($credential, true))->not->toBeFalse();
});

it('generates production security credential successfully', function () {
    config()->set('mpesa.env', 'production');

    $client = mpesaClient([
        'apiPassword' => 'api-password',
    ]);

    $credential = $client->getSecurityCredential();

    expect($credential)
        ->toBeString()
        ->not->toBeEmpty();

    expect(base64_decode($credential, true))->not->toBeFalse();
});

it('caches generated security credential on the client instance', function () {
    $client = mpesaClient([
        'apiPassword' => 'api-password',
    ]);

    $firstCredential = $client->getSecurityCredential();
    $secondCredential = $client->getSecurityCredential();

    expect($secondCredential)->toBe($firstCredential);
});

it('throws exception when generating security credential without api password', function () {
    $client = mpesaClient([
        'apiPassword' => '',
    ]);

    $client->getSecurityCredential();
})->throws(InvalidArgumentException::class, 'M-Pesa API password is required.');
