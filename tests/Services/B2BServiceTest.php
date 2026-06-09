<?php

use Akika\LaravelMpesaMultivendor\Services\B2BService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'b2b');

afterEach(function () {
    \Mockery::close();
});

it('sends b2b paybill request successfully', function () {
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

    $client->shouldReceive('getIdentifierType')
        ->with('shortcode')
        ->once()
        ->andReturn(4);

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('600000');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with(
            'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest',
            [
                'Initiator' => 'api-user',
                'SecurityCredential' => 'security-credential',
                'CommandID' => 'BusinessPayBill',
                'SenderIdentifierType' => 4,
                'RecieverIdentifierType' => 4,
                'Amount' => 101,
                'PartyA' => '600000',
                'PartyB' => '700000',
                'Remarks' => 'Test payment',
                'QueueTimeOutURL' => 'https://example.com/timeout',
                'ResultURL' => 'https://example.com/result',
                'AccountReference' => 'INV001',
                'Requester' => null,
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
            'ResponseDescription' => 'Accepted',
        ]);

    $service = new B2BService($client);

    $response = $service->send(
        toPaybill: true,
        receiverShortCode: '700000',
        amount: 100.50,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'https://example.com/timeout',
        remarks: 'Test payment',
        accountReference: 'INV001'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accepted',
    ]);
});

it('sends b2b buy goods request successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')->twice()->andReturnTrue();
    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('apiUsername')->once()->andReturn('api-user');
    $client->shouldReceive('getSecurityCredential')->once()->andReturn('security-credential');
    $client->shouldReceive('getIdentifierType')->with('tillnumber')->once()->andReturn(2);
    $client->shouldReceive('shortcode')->once()->andReturn('600000');
    $client->shouldReceive('sanitizePhoneNumber')->with('0712345678')->once()->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->withArgs(function ($url, $data) {
            return $url === 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest'
                && $data['CommandID'] === 'BusinessBuyGoods'
                && $data['RecieverIdentifierType'] === 2
                && $data['Requester'] === '254712345678';
        })
        ->andReturn([
            'ResponseCode' => '0',
        ]);

    $service = new B2BService($client);

    $response = $service->send(
        toPaybill: false,
        receiverShortCode: '700000',
        amount: 100,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'https://example.com/timeout',
        receiverIdentifierType: 'tillnumber',
        requester: '0712345678'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
    ]);
});

it('throws exception for invalid b2b result url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2BService($client);

    $service->send(
        toPaybill: true,
        receiverShortCode: '700000',
        amount: 100,
        resultUrl: 'invalid-url',
        queueTimeoutUrl: 'https://example.com/timeout'
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws exception for invalid b2b queue timeout url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/result')
        ->once()
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2BService($client);

    $service->send(
        toPaybill: true,
        receiverShortCode: '700000',
        amount: 100,
        resultUrl: 'https://example.com/result',
        queueTimeoutUrl: 'invalid-url'
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');

it('sends b2b express checkout request successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('https://example.com/callback')
        ->once()
        ->andReturnTrue();

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
                'receiverShortCode' => '700000',
                'partnerName' => 'Partner Ltd',
                'amount' => 100,
                'paymentRef' => 'PAY001',
                'callbackUrl' => 'https://example.com/callback',
                'RequestRefID' => 'REQ001',
            ]
        )
        ->andReturn([
            'ResponseCode' => '0',
        ]);

    $service = new B2BService($client);

    $response = $service->expressCheckout(
        partnerName: 'Partner Ltd',
        destShortcode: '700000',
        amount: 100.90,
        paymentReference: 'PAY001',
        callbackUrl: 'https://example.com/callback',
        requestRefID: 'REQ001'
    );

    expect($response)->toBe([
        'ResponseCode' => '0',
    ]);
});

it('throws exception for invalid b2b express checkout callback url', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->with('invalid-url')
        ->once()
        ->andReturnFalse();

    $service = new B2BService($client);

    $service->expressCheckout(
        partnerName: 'Partner Ltd',
        destShortcode: '700000',
        amount: 100,
        paymentReference: 'PAY001',
        callbackUrl: 'invalid-url',
        requestRefID: 'REQ001'
    );
})->throws(InvalidArgumentException::class, 'Invalid callback Url');
