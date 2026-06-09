<?php

use Akika\LaravelMpesaMultivendor\Services\ImsiService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('queries imsi successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/imsi/v1/checkATI';

    $expectedData = [
        'customerNumber' => '254712345678',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
        'IMSI' => '639020123456789',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new ImsiService($client);

    $response = $service->query('0712345678');

    expect($response)->toBe($expectedResponse);
});

it('uses sanitized phone number in imsi query request', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/imsi/v1/checkATI';

    $expectedData = [
        'customerNumber' => '254700000001',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('+254700000001')
        ->andReturn('254700000001');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new ImsiService($client);

    $response = $service->query('+254700000001');

    expect($response)->toBe($expectedResponse);
});

it('returns imsi api response unchanged', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/imsi/v1/checkATI';

    $expectedResponse = [
        'ResponseCode' => '500',
        'ResponseDescription' => 'Internal Server Error',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, [
            'customerNumber' => '254712345678',
        ])
        ->andReturn($expectedResponse);

    $service = new ImsiService($client);

    $response = $service->query('0712345678');

    expect($response)->toBe($expectedResponse);
});
