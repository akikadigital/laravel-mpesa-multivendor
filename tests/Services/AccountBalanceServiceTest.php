<?php

use Akika\LaravelMpesaMultivendor\Services\AccountBalanceService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'account-balance');

afterEach(function () {
    \Mockery::close();
});

it('checks account balance successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/result')
        ->once()
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/timeout')
        ->once()
        ->andReturnTrue();

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('apiUsername')
        ->once()
        ->andReturn('api-user');

    $client->shouldReceive('getSecurityCredential')
        ->once()
        ->andReturn('encrypted-security-credential');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('600000');

    $client->shouldReceive('getIdentifierType')
        ->with('shortcode')
        ->once()
        ->andReturn(4);

    $client->shouldReceive('makeRequest')
        ->once()
        ->with(
            'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
            [
                'Initiator' => 'api-user',
                'SecurityCredential' => 'encrypted-security-credential',
                'CommandID' => 'AccountBalance',
                'PartyA' => '600000',
                'IdentifierType' => 4,
                'Remarks' => 'Account balance request',
                'QueueTimeOutURL' => 'https://example.com/timeout',
                'ResultURL' => 'https://example.com/result',
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accept the service request successfully.',
        ]);

    $service = new AccountBalanceService($client);

    $response = $service->query(
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'https://example.com/timeout'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ]);
});

it('throws exception for invalid result url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new AccountBalanceService($client);

    $service->query(
        resultUrl: 'invalid-url',
        queueTimeoutUrl: 'https://example.com/timeout'
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws exception for invalid queue timeout url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/result')
        ->once()
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new AccountBalanceService($client);

    $service->query(
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'invalid-url'
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');
