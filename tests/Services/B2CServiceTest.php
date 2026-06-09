<?php

use Akika\LaravelMpesaMultivendor\Services\B2CService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'b2c');

afterEach(function () {
    \Mockery::close();
});

it('sends b2c payment request successfully', function () {
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
        ->andReturn('security-credential');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('600000');

    $client->shouldReceive('sanitizePhoneNumber')
        ->with('0712345678')
        ->once()
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with(
            'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            [
                'InitiatorName' => 'api-user',
                'SecurityCredential' => 'security-credential',
                'CommandID' => 'BusinessPayment',
                'Amount' => 101,
                'PartyA' => '600000',
                'PartyB' => '254712345678',
                'Remarks' => 'Salary payment',
                'QueueTimeOutURL' => 'https://example.com/timeout',
                'ResultURL' => 'https://example.com/result',
                'Occasion' => 'PAY001',
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accepted',
        ]);

    $service = new B2CService($client);

    $response = $service->send(
        phoneNumber: '0712345678',
        amount: 100.50,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'https://example.com/timeout',
        remarks: 'Salary payment',
        occasion: 'PAY001'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accepted',
    ]);
});

it('throws exception for invalid b2c result url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2CService($client);

    $service->send(
        phoneNumber: '0712345678',
        amount: 100,
        resultUrl: 'invalid-url',
        queueTimeoutUrl: 'https://example.com/timeout'
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws exception for invalid b2c queue timeout url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/result')
        ->once()
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2CService($client);

    $service->send(
        phoneNumber: '0712345678',
        amount: 100,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'invalid-url'
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');

it('sends b2c topup request successfully', function () {
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
        ->andReturn('security-credential');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('600000');

    $client->shouldReceive('sanitizePhoneNumber')
        ->with('0712345678')
        ->once()
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with(
            'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            [
                'Initiator' => 'api-user',
                'SecurityCredential' => 'security-credential',
                'CommandID' => 'BusinessPayToBulk',
                'SenderIdentifierType' => 4,
                'RecieverIdentifierType' => 4,
                'Amount' => 100,
                'PartyA' => '600000',
                'PartyB' => '700000',
                'AccountReference' => 'TOPUP001',
                'Requester' => '254712345678',
                'Remarks' => 'B2C TopUp',
                'QueueTimeOutURL' => 'https://example.com/timeout',
                'ResultURL' => 'https://example.com/result',
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accepted',
        ]);

    $service = new B2CService($client);

    $response = $service->topUp(
        receiverShortCode: '700000',
        amount: 100.90,
        resultUrl: 'https://example.com/result',
        timeoutUrl: 'https://example.com/timeout',
        accountReference: 'TOPUP001',
        requester: '0712345678'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accepted',
    ]);
});

it('throws exception for invalid b2c topup result url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2CService($client);

    $service->topUp(
        receiverShortCode: '700000',
        amount: 100,
        resultUrl: 'invalid-url',
        timeoutUrl: 'https://example.com/timeout'
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws exception for invalid b2c topup timeout url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/result')
        ->once()
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2CService($client);

    $service->topUp(
        receiverShortCode: '700000',
        amount: 100,
        resultUrl: 'https://example.com/result',
        timeoutUrl: 'invalid-url'
    );
})->throws(InvalidArgumentException::class, 'Invalid TimeOutURL.');
