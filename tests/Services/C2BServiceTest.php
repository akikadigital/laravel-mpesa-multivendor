<?php

use Akika\LaravelMpesaMultivendor\Services\C2BService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'c2b');

afterEach(function () {
    \Mockery::close();
});

it('registers c2b urls successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $confirmationUrl = 'https://example.com/mpesa/c2b/confirmation';
    $validationUrl = 'https://example.com/mpesa/c2b/validation';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

    $expectedData = [
        'ShortCode' => '174379',
        'ResponseType' => 'Completed',
        'ConfirmationURL' => $confirmationUrl,
        'ValidationURL' => $validationUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
    ];

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($confirmationUrl, 'Invalid ConfirmationURL.')
        ->andReturnNull();

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($validationUrl, 'Invalid ValidationURL.')
        ->andReturnNull();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new C2BService($client);

    $response = $service->registerUrls(
        confirmationUrl: $confirmationUrl,
        validationUrl: $validationUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when confirmation url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->once()
        ->with('invalid-confirmation-url', 'Invalid ConfirmationURL.')
        ->andThrow(new InvalidArgumentException('Invalid ConfirmationURL.'));

    $service = new C2BService($client);

    $service->registerUrls(
        confirmationUrl: 'invalid-confirmation-url',
        validationUrl: 'https://example.com/mpesa/c2b/validation',
    );
})->throws(InvalidArgumentException::class, 'Invalid ConfirmationURL.');

it('throws an exception when validation url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $confirmationUrl = 'https://example.com/mpesa/c2b/confirmation';

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($confirmationUrl, 'Invalid ConfirmationURL.')
        ->andReturnNull();

    $client->shouldReceive('validateUrl')
        ->once()
        ->with('invalid-validation-url', 'Invalid ValidationURL.')
        ->andThrow(new InvalidArgumentException('Invalid ValidationURL.'));

    $service = new C2BService($client);

    $service->registerUrls(
        confirmationUrl: $confirmationUrl,
        validationUrl: 'invalid-validation-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid ValidationURL.');

it('registers c2b urls with cancelled response type', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $confirmationUrl = 'https://example.com/mpesa/c2b/confirmation';
    $validationUrl = 'https://example.com/mpesa/c2b/validation';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

    $expectedData = [
        'ShortCode' => '174379',
        'ResponseType' => 'Cancelled',
        'ConfirmationURL' => $confirmationUrl,
        'ValidationURL' => $validationUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
    ];

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($confirmationUrl, 'Invalid ConfirmationURL.')
        ->andReturnNull();

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($validationUrl, 'Invalid ValidationURL.')
        ->andReturnNull();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new C2BService($client);

    $response = $service->registerUrls(
        confirmationUrl: $confirmationUrl,
        validationUrl: $validationUrl,
        responseType: 'Cancelled',
    );

    expect($response)->toBe($expectedResponse);
});

it('simulates c2b buy goods transaction successfully when bill ref number is null', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';

    $expectedData = [
        'ShortCode' => '174379',
        'CommandID' => 'CustomerBuyGoodsOnline',
        'Amount' => 101,
        'Msisdn' => '254712345678',
        'BillRefNumber' => null,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

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

    $service = new C2BService($client);

    $response = $service->simulate(
        phoneNumber: '0712345678',
        amount: 100.50,
    );

    expect($response)->toBe($expectedResponse);
});

it('simulates c2b pay bill transaction successfully when bill ref number is provided', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';

    $expectedData = [
        'ShortCode' => '174379',
        'CommandID' => 'CustomerPayBillOnline',
        'Amount' => 250,
        'Msisdn' => '254712345678',
        'BillRefNumber' => 'INV-001',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

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

    $service = new C2BService($client);

    $response = $service->simulate(
        phoneNumber: '0712345678',
        amount: 250,
        billRefNumber: 'INV-001',
    );

    expect($response)->toBe($expectedResponse);
});
