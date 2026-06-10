<?php

use Akika\LaravelMpesaMultivendor\Services\DynamicQrService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'dynamic-qr');

afterEach(function () {
    \Mockery::close();
});

it('generates a dynamic qr code using default shortcode and settings', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate';

    $expectedData = [
        'MerchantName' => 'Akika Digital',
        'RefNo' => 'INV-001',
        'Amount' => 101,
        'TrxCode' => 'PB',
        'CPI' => '174379',
        'Size' => 300,
    ];

    $expectedResponse = [
        'ResponseCode' => '00',
        'ResponseDescription' => 'Success',
        'QRCode' => 'base64-encoded-qr-code',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new DynamicQrService($client);

    $response = $service->generate(
        merchantName: 'Akika Digital',
        reference: 'INV-001',
        amount: 100.50,
    );

    expect($response)->toBe($expectedResponse);
});

it('generates a dynamic qr code with custom credit party identifier', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate';

    $expectedData = [
        'MerchantName' => 'Akika Digital',
        'RefNo' => 'INV-002',
        'Amount' => 250,
        'TrxCode' => 'PB',
        'CPI' => '600000',
        'Size' => 300,
    ];

    $expectedResponse = [
        'ResponseCode' => '00',
        'QRCode' => 'base64-encoded-qr-code',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldNotReceive('shortcode');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new DynamicQrService($client);

    $response = $service->generate(
        merchantName: 'Akika Digital',
        reference: 'INV-002',
        amount: 250,
        creditPartyIdentifier: '600000',
    );

    expect($response)->toBe($expectedResponse);
});

it('generates a dynamic qr code with custom transaction code', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate';

    $expectedData = [
        'MerchantName' => 'Akika Digital',
        'RefNo' => 'INV-003',
        'Amount' => 500,
        'TrxCode' => 'BG',
        'CPI' => '174379',
        'Size' => 300,
    ];

    $expectedResponse = [
        'ResponseCode' => '00',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new DynamicQrService($client);

    $response = $service->generate(
        merchantName: 'Akika Digital',
        reference: 'INV-003',
        amount: 500,
        transactionCode: 'BG',
    );

    expect($response)->toBe($expectedResponse);
});

it('generates a dynamic qr code with custom size', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate';

    $expectedData = [
        'MerchantName' => 'Akika Digital',
        'RefNo' => 'INV-004',
        'Amount' => 1000,
        'TrxCode' => 'PB',
        'CPI' => '174379',
        'Size' => 500,
    ];

    $expectedResponse = [
        'ResponseCode' => '00',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new DynamicQrService($client);

    $response = $service->generate(
        merchantName: 'Akika Digital',
        reference: 'INV-004',
        amount: 1000,
        size: 500,
    );

    expect($response)->toBe($expectedResponse);
});

it('rounds amount up before generating qr code', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/qrcode/v1/generate';

    $expectedData = [
        'MerchantName' => 'Akika Digital',
        'RefNo' => 'INV-005',
        'Amount' => 11,
        'TrxCode' => 'PB',
        'CPI' => '174379',
        'Size' => 300,
    ];

    $expectedResponse = [
        'ResponseCode' => '00',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new DynamicQrService($client);

    $response = $service->generate(
        merchantName: 'Akika Digital',
        reference: 'INV-005',
        amount: 10.01,
    );

    expect($response)->toBe($expectedResponse);
});
