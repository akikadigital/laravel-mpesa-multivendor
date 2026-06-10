<?php

use Akika\LaravelMpesaMultivendor\Services\B2BService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'b2b');

afterEach(function () {
    \Mockery::close();
});

it('sends b2b buy goods request successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->with('https://example.com/result', 'Invalid ResultURL.')
        ->once()
        ->andReturnNull();

    $client->shouldReceive('validateUrl')
        ->with('https://example.com/timeout', 'Invalid QueueTimeOutURL.')
        ->once()
        ->andReturnNull();

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
                'SecurityCredential' => 'encrypted-security-credential',
                'CommandID' => 'BusinessBuyGoods',
                'SenderIdentifierType' => 4,
                'RecieverIdentifierType' => 2,
                'Amount' => 101,
                'PartyA' => '600000',
                'PartyB' => '123456',
                'Remarks' => 'B2B buy goods',
                'QueueTimeOutURL' => 'https://example.com/timeout',
                'ResultURL' => 'https://example.com/result',
                'AccountReference' => null,
                'Requester' => '254712345678',
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accept the service request successfully.',
        ]);

    $service = new B2BService($client);

    $response = $service->buyGoods(
        receiverShortCode: '123456',
        amount: 100.50,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'https://example.com/timeout',
        requester: '0712345678'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ]);
});

it('sends b2b paybill request successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->with('https://example.com/result', 'Invalid ResultURL.')
        ->once()
        ->andReturnNull();

    $client->shouldReceive('validateUrl')
        ->with('https://example.com/timeout', 'Invalid QueueTimeOutURL.')
        ->once()
        ->andReturnNull();

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

    $client->shouldReceive('makeRequest')
        ->once()
        ->with(
            'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            [
                'Initiator' => 'api-user',
                'SecurityCredential' => 'encrypted-security-credential',
                'CommandID' => 'BusinessPayBill',
                'SenderIdentifierType' => 4,
                'RecieverIdentifierType' => 4,
                'Amount' => 250,
                'PartyA' => '600000',
                'PartyB' => '654321',
                'Remarks' => 'B2B pay bill',
                'QueueTimeOutURL' => 'https://example.com/timeout',
                'ResultURL' => 'https://example.com/result',
                'AccountReference' => 'INV001',
                'Requester' => null,
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accept the service request successfully.',
        ]);

    $service = new B2BService($client);

    $response = $service->paybill(
        receiverShortCode: '654321',
        amount: 250,
        accountReference: 'INV001',
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'https://example.com/timeout'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ]);
});

it('sends b2b express checkout request successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->with('https://example.com/callback', 'Invalid CallbackURL.')
        ->once()
        ->andReturnNull();

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('600000');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with(
            'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/ussdpush/get-msisdn',
            [
                'primaryShortCode' => '600000',
                'receiverShortCode' => '123456',
                'partnerName' => 'Test Partner',
                'amount' => 100,
                'paymentRef' => 'PAY-001',
                'callbackUrl' => 'https://example.com/callback',
                'RequestRefID' => 'REQ-001',
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accept the service request successfully.',
        ]);

    $service = new B2BService($client);

    $response = $service->expressCheckout(
        partnerName: 'Test Partner',
        destShortcode: '123456',
        amount: 100.90,
        paymentReference: 'PAY-001',
        callbackUrl: 'https://example.com/callback',
        requestRefID: 'REQ-001'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ]);
});

it('throws exception for invalid result url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->with('invalid-url', 'Invalid ResultURL.')
        ->once()
        ->andThrow(new InvalidArgumentException('Invalid ResultURL.'));

    $service = new B2BService($client);

    $service->buyGoods(
        receiverShortCode: '123456',
        amount: 100,
        resultUrl: 'invalid-url',
        queueTimeoutUrl: 'https://example.com/timeout'
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws exception for invalid queue timeout url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->with('https://example.com/result', 'Invalid ResultURL.')
        ->once()
        ->andReturnNull();

    $client->shouldReceive('validateUrl')
        ->with('invalid-url', 'Invalid QueueTimeOutURL.')
        ->once()
        ->andThrow(new InvalidArgumentException('Invalid QueueTimeOutURL.'));

    $service = new B2BService($client);

    $service->buyGoods(
        receiverShortCode: '123456',
        amount: 100,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'invalid-url'
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');

it('throws exception for invalid express checkout callback url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->with('invalid-url', 'Invalid CallbackURL.')
        ->once()
        ->andThrow(new InvalidArgumentException('Invalid CallbackURL.'));

    $service = new B2BService($client);

    $service->expressCheckout(
        partnerName: 'Test Partner',
        destShortcode: '123456',
        amount: 100,
        paymentReference: 'PAY-001',
        callbackUrl: 'invalid-url',
        requestRefID: 'REQ-001'
    );
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');
