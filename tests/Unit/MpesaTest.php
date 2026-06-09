<?php

use Akika\LaravelMpesaMultivendor\Mpesa;
use Akika\LaravelMpesaMultivendor\Services\AccountBalanceService;
use Akika\LaravelMpesaMultivendor\Services\B2BService;
use Akika\LaravelMpesaMultivendor\Services\B2CService;
use Akika\LaravelMpesaMultivendor\Services\BillManagerService;
use Akika\LaravelMpesaMultivendor\Services\BongaService;
use Akika\LaravelMpesaMultivendor\Services\C2BService;
use Akika\LaravelMpesaMultivendor\Services\DynamicQrService;
use Akika\LaravelMpesaMultivendor\Services\ImsiService;
use Akika\LaravelMpesaMultivendor\Services\OrganizationServce;
use Akika\LaravelMpesaMultivendor\Services\PochiService;
use Akika\LaravelMpesaMultivendor\Services\RatibaService;
use Akika\LaravelMpesaMultivendor\Services\ReversalService;
use Akika\LaravelMpesaMultivendor\Services\StkPushService;
use Akika\LaravelMpesaMultivendor\Services\TaxRemittanceService;
use Akika\LaravelMpesaMultivendor\Services\TransactionHistoryService;
use Akika\LaravelMpesaMultivendor\Services\TransactionStatusService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;
use Akika\LaravelMpesaMultivendor\Support\MpesaCredentials;

uses()->group('mpesa');

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

function mpesaCredentialArray(array $overrides = []): array
{
    return array_merge([
        'shortcode' => '600000',
        'consumer_key' => 'consumer-key',
        'consumer_secret' => 'consumer-secret',
        'api_username' => 'api-user',
        'api_password' => 'api-password',
        'passkey' => 'test-passkey',
    ], $overrides);
}

it('creates mpesa instance using array credentials', function () {
    $mpesa = Mpesa::using(mpesaCredentialArray());

    expect($mpesa)->toBeInstanceOf(Mpesa::class)
        ->and($mpesa->client())->toBeInstanceOf(MpesaClient::class)
        ->and($mpesa->client()->shortcode())->toBe('600000')
        ->and($mpesa->client()->apiUsername())->toBe('api-user')
        ->and($mpesa->client()->passkey())->toBe('test-passkey');
});

it('creates mpesa instance using mpesa credentials object', function () {
    $credentials = MpesaCredentials::fromArray(mpesaCredentialArray([
        'shortcode' => '700000',
        'api_username' => 'custom-api-user',
    ]));

    $mpesa = Mpesa::using($credentials);

    expect($mpesa)->toBeInstanceOf(Mpesa::class)
        ->and($mpesa->client())->toBeInstanceOf(MpesaClient::class)
        ->and($mpesa->client()->credentials())->toBe($credentials)
        ->and($mpesa->client()->shortcode())->toBe('700000')
        ->and($mpesa->client()->apiUsername())->toBe('custom-api-user');
});

it('creates mpesa instance using default config credentials', function () {
    $mpesa = Mpesa::default();

    expect($mpesa)->toBeInstanceOf(Mpesa::class)
        ->and($mpesa->client())->toBeInstanceOf(MpesaClient::class)
        ->and($mpesa->client()->shortcode())->toBe('600000')
        ->and($mpesa->client()->apiUsername())->toBe('api-user');
});

it('returns the underlying mpesa client', function () {
    $client = new MpesaClient(MpesaCredentials::fromArray(mpesaCredentialArray()));

    $mpesa = new Mpesa($client);

    expect($mpesa->client())->toBe($client);
});

it('returns service instances', function () {
    $mpesa = Mpesa::using(mpesaCredentialArray());

    expect($mpesa->accountBalance())->toBeInstanceOf(AccountBalanceService::class)
        ->and($mpesa->stk())->toBeInstanceOf(StkPushService::class)
        ->and($mpesa->c2b())->toBeInstanceOf(C2BService::class)
        ->and($mpesa->b2c())->toBeInstanceOf(B2CService::class)
        ->and($mpesa->b2b())->toBeInstanceOf(B2BService::class)
        ->and($mpesa->reversal())->toBeInstanceOf(ReversalService::class)
        ->and($mpesa->transactionStatus())->toBeInstanceOf(TransactionStatusService::class)
        ->and($mpesa->dynamicQr())->toBeInstanceOf(DynamicQrService::class)
        ->and($mpesa->billManager())->toBeInstanceOf(BillManagerService::class)
        ->and($mpesa->taxRemittance())->toBeInstanceOf(TaxRemittanceService::class)
        ->and($mpesa->ratiba())->toBeInstanceOf(RatibaService::class)
        ->and($mpesa->transactionHistory())->toBeInstanceOf(TransactionHistoryService::class)
        ->and($mpesa->pochi())->toBeInstanceOf(PochiService::class)
        ->and($mpesa->bonga())->toBeInstanceOf(BongaService::class)
        ->and($mpesa->imsi())->toBeInstanceOf(ImsiService::class)
        ->and($mpesa->org())->toBeInstanceOf(OrganizationServce::class);
});
