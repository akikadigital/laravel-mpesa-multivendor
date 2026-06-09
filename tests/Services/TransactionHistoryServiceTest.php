<?php

use Akika\LaravelMpesaMultivendor\Services\TransactionHistoryService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('registers transaction history callback successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/mpesa/history/callback';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/pulltransactions/v1/register';

    $expectedData = [
        'ShortCode' => '174379',
        'ResponseType' => 'Pull',
        'NominatedNumber' => '254712345678',
        'CallBackURL' => $callbackUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
    ];

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($callbackUrl)
        ->andReturnTrue();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new TransactionHistoryService($client);

    $response = $service->registerCallbackUrl(
        nominatedNumber: '0712345678',
        callbackUrl: $callbackUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when transaction history callback url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-callback-url')
        ->andReturnFalse();

    $service = new TransactionHistoryService($client);

    $service->registerCallbackUrl(
        nominatedNumber: '0712345678',
        callbackUrl: 'invalid-callback-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');

it('queries transaction history successfully using default offset', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/pulltransactions/v1/query';

    $expectedData = [
        'ShortCode' => '174379',
        'StartDate' => '2026-06-01 00:00:00',
        'EndDate' => '2026-06-09 23:59:59',
        'OffSetValue' => 0,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
        'Transactions' => [],
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new TransactionHistoryService($client);

    $response = $service->query(
        startDate: '2026-06-01 00:00:00',
        endDate: '2026-06-09 23:59:59',
    );

    expect($response)->toBe($expectedResponse);
});

it('queries transaction history successfully using custom offset', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/pulltransactions/v1/query';

    $expectedData = [
        'ShortCode' => '174379',
        'StartDate' => '2026-06-01 08:30:00',
        'EndDate' => '2026-06-09 18:45:10',
        'OffSetValue' => 50,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'Transactions' => [
            [
                'ReceiptNo' => 'RBX123456',
                'Amount' => 100,
            ],
        ],
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new TransactionHistoryService($client);

    $response = $service->query(
        startDate: '2026-06-01 08:30:00',
        endDate: '2026-06-09 18:45:10',
        offset: 50,
    );

    expect($response)->toBe($expectedResponse);
});
