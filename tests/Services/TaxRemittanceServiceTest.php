<?php

use Akika\LaravelMpesaMultivendor\Services\TaxRemittanceService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('remits tax successfully using default shortcode', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/tax/result';
    $queueTimeoutUrl = 'https://example.com/mpesa/tax/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/remittax';

    $expectedData = [
        'Initiator' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'PayTaxToKRA',
        'SenderIdentifierType' => 4,
        'RecieverIdentifierType' => 4,
        'Amount' => 101,
        'PartyA' => '174379',
        'PartyB' => '572572',
        'AccountReference' => 'PRN-001',
        'Remarks' => 'Tax remittance',
        'QueueTimeOutURL' => $queueTimeoutUrl,
        'ResultURL' => $resultUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Accept the service request successfully.',
    ];

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($resultUrl)
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($queueTimeoutUrl)
        ->andReturnTrue();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('apiUsername')->once()->andReturn('testapi');
    $client->shouldReceive('getSecurityCredential')->once()->andReturn('security-credential');
    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new TaxRemittanceService($client);

    $response = $service->remit(
        amount: 100.50,
        accountReference: 'PRN-001',
        resultUrl: $resultUrl,
        queueTimeoutUrl: $queueTimeoutUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when tax remittance result url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-result-url')
        ->andReturnFalse();

    $service = new TaxRemittanceService($client);

    $service->remit(
        amount: 100,
        accountReference: 'PRN-001',
        resultUrl: 'invalid-result-url',
        queueTimeoutUrl: 'https://example.com/mpesa/tax/timeout',
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws an exception when tax remittance queue timeout url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/tax/result';

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($resultUrl)
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-timeout-url')
        ->andReturnFalse();

    $service = new TaxRemittanceService($client);

    $service->remit(
        amount: 100,
        accountReference: 'PRN-001',
        resultUrl: $resultUrl,
        queueTimeoutUrl: 'invalid-timeout-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');

it('remits tax successfully using custom party a, command id and remarks', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/tax/result';
    $queueTimeoutUrl = 'https://example.com/mpesa/tax/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/remittax';

    $expectedData = [
        'Initiator' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'PayTaxToKRA',
        'SenderIdentifierType' => 4,
        'RecieverIdentifierType' => 4,
        'Amount' => 500,
        'PartyA' => '600000',
        'PartyB' => '572572',
        'AccountReference' => 'PRN-002',
        'Remarks' => 'VAT payment',
        'QueueTimeOutURL' => $queueTimeoutUrl,
        'ResultURL' => $resultUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ConversationID' => 'test-conversation-id',
    ];

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($resultUrl)
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($queueTimeoutUrl)
        ->andReturnTrue();

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');
    $client->shouldReceive('apiUsername')->once()->andReturn('testapi');
    $client->shouldReceive('getSecurityCredential')->once()->andReturn('security-credential');
    $client->shouldNotReceive('shortcode');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new TaxRemittanceService($client);

    $response = $service->remit(
        amount: 500,
        accountReference: 'PRN-002',
        resultUrl: $resultUrl,
        queueTimeoutUrl: $queueTimeoutUrl,
        remarks: 'VAT payment',
        commandId: 'PayTaxToKRA',
        partyA: '600000',
    );

    expect($response)->toBe($expectedResponse);
});
