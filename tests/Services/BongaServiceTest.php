<?php

use Akika\LaravelMpesaMultivendor\Services\BongaService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('calculates bonga points successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/lipa/na/bonga/calculate-points';

    $expectedData = [
        'points' => 500,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'Points' => 500,
        'Amount' => 100,
        'ResponseDescription' => 'Success',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BongaService($client);

    $response = $service->calculate(500);

    expect($response)->toBe($expectedResponse);
});

it('redeems bonga points successfully using default conversion rate', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/lipa/na/bonga/redeem-paybill';

    $expectedData = [
        'msisdn' => '254712345678',
        'amount' => 100.00,
        'bongaPoints' => 20,
        'conversionRate' => 0.2,
        'shortCode' => '174379',
        'accountNumber' => 'ORDER-001',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Bonga points redeemed successfully',
        'TransactionReference' => 'ORDER-001',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BongaService($client);

    $response = $service->pay(
        phoneNumber: '0712345678',
        amount: 100.00,
        transactionReference: 'ORDER-001',
    );

    expect($response)->toBe($expectedResponse);
});

it('redeems bonga points successfully using custom conversion rate', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/lipa/na/bonga/redeem-paybill';

    $expectedData = [
        'msisdn' => '254712345678',
        'amount' => 150.00,
        'bongaPoints' => 30,
        'conversionRate' => 0.2,
        'shortCode' => '174379',
        'accountNumber' => 'ORDER-002',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Bonga points redeemed successfully',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BongaService($client);

    $response = $service->pay(
        phoneNumber: '0712345678',
        amount: 150.00,
        transactionReference: 'ORDER-002',
        conversionRate: 0.2,
    );

    expect($response)->toBe($expectedResponse);
});

it('rounds bonga points up when calculation results in a decimal', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/lipa/na/bonga/redeem-paybill';

    $expectedData = [
        'msisdn' => '254712345678',
        'amount' => 101.00,
        'bongaPoints' => 21, // ceil(101 * 0.2)
        'conversionRate' => 0.2,
        'shortCode' => '174379',
        'accountNumber' => 'ORDER-003',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BongaService($client);

    $response = $service->pay(
        phoneNumber: '0712345678',
        amount: 101.00,
        transactionReference: 'ORDER-003',
    );

    expect($response)->toBe($expectedResponse);
});
