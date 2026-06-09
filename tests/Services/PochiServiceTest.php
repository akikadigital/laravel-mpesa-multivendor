<?php

use Akika\LaravelMpesaMultivendor\Services\PochiService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('sends pochi payment successfully using default shortcode', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/pochi/result';
    $timeoutUrl = 'https://example.com/mpesa/pochi/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/pochiv1/accountbalance';

    $expectedData = [
        'OriginatorConversationID' => 'TXN-001',
        'InitiatorName' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'BusinessPayToPochi',
        'Amount' => 100.50,
        'PartyA' => '174379',
        'PartyB' => '254712345678',
        'Remarks' => 'B2P Pochi payment',
        'QueueTimeOutURL' => $timeoutUrl,
        'ResultURL' => $resultUrl,
        'Occassion' => '',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('apiUsername')->once()->andReturn('testapi');
    $client->shouldReceive('getSecurityCredential')->once()->andReturn('security-credential');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new PochiService($client);

    $response = $service->send(
        transctionId: 'TXN-001',
        msisdn: '0712345678',
        amount: 100.50,
        resultUrl: $resultUrl,
        timeoutUrl: $timeoutUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('sends pochi payment successfully using custom shortcode, remarks and occasion', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/pochi/result';
    $timeoutUrl = 'https://example.com/mpesa/pochi/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/pochiv1/accountbalance';

    $expectedData = [
        'OriginatorConversationID' => 'TXN-002',
        'InitiatorName' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'BusinessPayToPochi',
        'Amount' => 250,
        'PartyA' => '600000',
        'PartyB' => '254700000001',
        'Remarks' => 'Pay customer pochi',
        'QueueTimeOutURL' => $timeoutUrl,
        'ResultURL' => $resultUrl,
        'Occassion' => 'ORDER-002',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ConversationID' => 'test-conversation-id',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('apiUsername')->once()->andReturn('testapi');
    $client->shouldReceive('getSecurityCredential')->once()->andReturn('security-credential');
    $client->shouldNotReceive('shortcode');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('+254700000001')
        ->andReturn('254700000001');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new PochiService($client);

    $response = $service->send(
        transctionId: 'TXN-002',
        msisdn: '+254700000001',
        amount: 250,
        resultUrl: $resultUrl,
        timeoutUrl: $timeoutUrl,
        remarks: 'Pay customer pochi',
        ocassion: 'ORDER-002',
        shortCode: '600000',
    );

    expect($response)->toBe($expectedResponse);
});

it('returns pochi api response unchanged', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/pochiv1/accountbalance';

    $expectedResponse = [
        'ResponseCode' => '500',
        'ResponseDescription' => 'Failed',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('apiUsername')->once()->andReturn('testapi');
    $client->shouldReceive('getSecurityCredential')->once()->andReturn('security-credential');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, \Mockery::on(fn($data) => $data['PartyB'] === '254712345678'))
        ->andReturn($expectedResponse);

    $service = new PochiService($client);

    $response = $service->send(
        transctionId: 'TXN-003',
        msisdn: '0712345678',
        amount: 100,
        resultUrl: 'https://example.com/mpesa/pochi/result',
        timeoutUrl: 'https://example.com/mpesa/pochi/timeout',
    );

    expect($response)->toBe($expectedResponse);
});
