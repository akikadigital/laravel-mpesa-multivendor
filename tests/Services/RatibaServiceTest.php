<?php

use Akika\LaravelMpesaMultivendor\Services\RatibaService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('creates a ratiba standing order successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/mpesa/ratiba/callback';
    $expectedUrl = 'https://sandbox.safaricom.co.ke/standingorder/v1/createStandingOrderExternal';

    $expectedData = [
        'StandingOrderName' => 'Monthly Subscription',
        'StartDate' => '20260609',
        'EndDate' => '20261231',
        'BusinessShortCode' => '174379',
        'TransactionType' => 'CustomerPayBillOnline',
        'ReceiverPartyIdentifierType' => 4,
        'Amount' => 100,
        'PartyA' => '254712345678',
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'SUB-001',
        'TransactionDesc' => 'Ratiba standing order',
        'Frequency' => 4,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Standing order created successfully',
        'StandingOrderID' => 'SO-001',
    ];

    $client->shouldReceive('isValidUrl')->once()->with($callbackUrl)->andReturnTrue();
    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('ratibaTransactionType')
        ->once()
        ->with('CustomerPayBillOnline')
        ->andReturn('CustomerPayBillOnline');

    $client->shouldReceive('getIdentifierType')
        ->once()
        ->with('CustomerPayBillOnline')
        ->andReturn(4);

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('ratibaFrequency')
        ->once()
        ->with('Monthly')
        ->andReturn(4);

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new RatibaService($client);

    $response = $service->createStandingOrder(
        name: 'Monthly Subscription',
        startDate: '2026-06-09',
        endDate: '2026-12-31',
        transactionType: 'CustomerPayBillOnline',
        amount: 100.90,
        phoneNumber: '0712345678',
        callbackUrl: $callbackUrl,
        accountReference: 'SUB-001',
        frequency: 'Monthly',
    );

    fwrite(
        STDERR,
        "\nRatiba Request:\n" .
            json_encode($response, JSON_PRETTY_PRINT) .
            "\n"
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when create standing order callback url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-callback-url')
        ->andReturnFalse();

    $service = new RatibaService($client);

    $service->createStandingOrder(
        name: 'Monthly Subscription',
        startDate: '2026-06-09',
        endDate: '2026-12-31',
        transactionType: 'CustomerPayBillOnline',
        amount: 100,
        phoneNumber: '0712345678',
        callbackUrl: 'invalid-callback-url',
        accountReference: 'SUB-001',
        frequency: 'Monthly',
    );
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');

it('creates a standing order with custom transaction description and daily frequency', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/mpesa/ratiba/callback';
    $expectedUrl = 'https://sandbox.safaricom.co.ke/standingorder/v1/createStandingOrderExternal';

    $expectedData = [
        'StandingOrderName' => 'Daily Savings',
        'StartDate' => '20260609',
        'EndDate' => '20260709',
        'BusinessShortCode' => '174379',
        'TransactionType' => 'CustomerPayBillOnline',
        'ReceiverPartyIdentifierType' => 4,
        'Amount' => 50,
        'PartyA' => '254700000001',
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'SAVE-001',
        'TransactionDesc' => 'Daily customer saving',
        'Frequency' => 2,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'StandingOrderID' => 'SO-002',
    ];

    $client->shouldReceive('isValidUrl')->once()->with($callbackUrl)->andReturnTrue();
    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('ratibaTransactionType')
        ->once()
        ->with('CustomerPayBillOnline')
        ->andReturn('CustomerPayBillOnline');

    $client->shouldReceive('getIdentifierType')
        ->once()
        ->with('CustomerPayBillOnline')
        ->andReturn(4);

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('+254700000001')
        ->andReturn('254700000001');

    $client->shouldReceive('ratibaFrequency')
        ->once()
        ->with('Daily')
        ->andReturn(2);

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new RatibaService($client);

    $response = $service->createStandingOrder(
        name: 'Daily Savings',
        startDate: '2026-06-09',
        endDate: '2026-07-09',
        transactionType: 'CustomerPayBillOnline',
        amount: 50.99,
        phoneNumber: '+254700000001',
        callbackUrl: $callbackUrl,
        accountReference: 'SAVE-001',
        frequency: 'Daily',
        transactionDesc: 'Daily customer saving',
    );

    expect($response)->toBe($expectedResponse);
});

it('queries a ratiba standing order successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/standingorder/v1/queryStandingOrder';

    $expectedData = [
        'StandingOrderID' => 'SO-001',
        'BusinessShortCode' => '174379',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'StandingOrderID' => 'SO-001',
        'Status' => 'Active',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new RatibaService($client);

    $response = $service->query('SO-001');

    expect($response)->toBe($expectedResponse);
});

it('cancels a ratiba standing order successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/mpesa/ratiba/cancel-callback';
    $expectedUrl = 'https://sandbox.safaricom.co.ke/standingorder/v1/cancelStandingOrderExternal';

    $expectedData = [
        'StandingOrderID' => 'SO-001',
        'BusinessShortCode' => '174379',
        'CallBackURL' => $callbackUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Standing order cancelled successfully',
    ];

    $client->shouldReceive('isValidUrl')->once()->with($callbackUrl)->andReturnTrue();
    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new RatibaService($client);

    $response = $service->cancel(
        standingOrderId: 'SO-001',
        callbackUrl: $callbackUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when cancel callback url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-callback-url')
        ->andReturnFalse();

    $service = new RatibaService($client);

    $service->cancel(
        standingOrderId: 'SO-001',
        callbackUrl: 'invalid-callback-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');
