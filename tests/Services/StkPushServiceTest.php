<?php

use Akika\LaravelMpesaMultivendor\Services\StkPushService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;
use Carbon\Carbon;

uses()->group('services', 'stk-push');

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 9, 10, 30, 45));
});

afterEach(function () {
    Carbon::setTestNow();
    \Mockery::close();
});

it('initiates an stk push request successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/mpesa/callback';
    $timestamp = '20260609103045';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $expectedData = [
        'BusinessShortCode' => '174379',
        'Password' => 'generated-password',
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => 101,
        'PartyA' => '254712345678',
        'PartyB' => '174379',
        'PhoneNumber' => '254712345678',
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'INV-001',
        'TransactionDesc' => 'STK Push Payment',
    ];

    $expectedResponse = [
        'MerchantRequestID' => 'test-merchant-request-id',
        'CheckoutRequestID' => 'test-checkout-request-id',
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
        'CustomerMessage' => 'Success. Request accepted for processing',
    ];

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($callbackUrl, 'Invalid CallbackURL.')
        ->andReturnNull();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->twice()
        ->andReturn('174379');

    $client->shouldReceive('generatePassword')
        ->once()
        ->with($timestamp)
        ->andReturn('generated-password');

    $client->shouldReceive('sanitizePhoneNumber')
        ->twice()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new StkPushService($client);

    $response = $service->push(
        phoneNumber: '0712345678',
        amount: 100.50,
        callbackUrl: $callbackUrl,
        accountReference: 'INV-001',
    );

    expect($response)->toBe($expectedResponse);
});

it('initiates an stk push request with custom receiving shortcode', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/mpesa/callback';
    $timestamp = '20260609103045';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $expectedData = [
        'BusinessShortCode' => '174379',
        'Password' => 'generated-password',
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerBuyGoodsOnline',
        'Amount' => 250,
        'PartyA' => '254712345678',
        'PartyB' => '600000',
        'PhoneNumber' => '254712345678',
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'INV-002',
        'TransactionDesc' => 'Custom STK payment',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing',
    ];

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($callbackUrl, 'Invalid CallbackURL.')
        ->andReturnNull();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('generatePassword')
        ->once()
        ->with($timestamp)
        ->andReturn('generated-password');

    $client->shouldReceive('sanitizePhoneNumber')
        ->twice()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new StkPushService($client);

    $response = $service->push(
        phoneNumber: '0712345678',
        amount: 250,
        callbackUrl: $callbackUrl,
        accountReference: 'INV-002',
        transactionDesc: 'Custom STK payment',
        transactionType: 'CustomerBuyGoodsOnline',
        receivingShortCode: '600000',
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when callback url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->once()
        ->with('invalid-url', 'Invalid CallbackURL.')
        ->andThrow(new InvalidArgumentException('Invalid CallbackURL.'));

    $service = new StkPushService($client);

    $service->push(
        phoneNumber: '0712345678',
        amount: 100,
        callbackUrl: 'invalid-url',
        accountReference: 'INV-001',
    );
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');

it('queries stk push payment status successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $timestamp = '20260609103045';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query';

    $expectedData = [
        'BusinessShortCode' => '174379',
        'Password' => 'generated-password',
        'Timestamp' => $timestamp,
        'CheckoutRequestID' => 'ws_CO_123456789',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'The service request has been accepted successfully',
        'MerchantRequestID' => 'test-merchant-request-id',
        'CheckoutRequestID' => 'ws_CO_123456789',
        'ResultCode' => '0',
        'ResultDesc' => 'The service request is processed successfully.',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('generatePassword')
        ->once()
        ->with($timestamp)
        ->andReturn('generated-password');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new StkPushService($client);

    $response = $service->query('ws_CO_123456789');

    expect($response)->toBe($expectedResponse);
});
