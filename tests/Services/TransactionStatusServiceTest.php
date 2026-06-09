<?php

use Akika\LaravelMpesaMultivendor\Services\TransactionStatusService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('queries transaction status successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/status/result';
    $queueTimeoutUrl = 'https://example.com/mpesa/status/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';

    $expectedData = [
        'Initiator' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'TransactionStatusQuery',
        'TransactionID' => 'RBX123456',
        'PartyA' => '174379',
        'IdentifierType' => 4,
        'OriginalConversationID' => '',
        'ResultURL' => $resultUrl,
        'QueueTimeOutURL' => $queueTimeoutUrl,
        'Remarks' => 'Transaction status query',
        'Occasion' => '',
    ];

    $expectedResponse = [
        'OriginatorConversationID' => 'test-originator-conversation-id',
        'ConversationID' => 'test-conversation-id',
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

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('apiUsername')
        ->once()
        ->andReturn('testapi');

    $client->shouldReceive('getSecurityCredential')
        ->once()
        ->andReturn('security-credential');

    $client->shouldReceive('shortcode')
        ->once()
        ->andReturn('174379');

    $client->shouldReceive('getIdentifierType')
        ->once()
        ->with('shortcode')
        ->andReturn(4);

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new TransactionStatusService($client);

    $response = $service->query(
        transactionId: 'RBX123456',
        resultUrl: $resultUrl,
        queueTimeoutUrl: $queueTimeoutUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when result url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-result-url')
        ->andReturnFalse();

    $service = new TransactionStatusService($client);

    $service->query(
        transactionId: 'RBX123456',
        resultUrl: 'invalid-result-url',
        queueTimeoutUrl: 'https://example.com/mpesa/status/timeout',
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws an exception when queue timeout url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/status/result';

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($resultUrl)
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-timeout-url')
        ->andReturnFalse();

    $service = new TransactionStatusService($client);

    $service->query(
        transactionId: 'RBX123456',
        resultUrl: $resultUrl,
        queueTimeoutUrl: 'invalid-timeout-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');

it('uses custom party a, identifier type, original conversation id, remarks and occasion', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/status/result';
    $queueTimeoutUrl = 'https://example.com/mpesa/status/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';

    $expectedData = [
        'Initiator' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'TransactionStatusQuery',
        'TransactionID' => 'RBX123456',
        'PartyA' => '600000',
        'IdentifierType' => 11,
        'OriginalConversationID' => 'AG_20260609_123456',
        'ResultURL' => $resultUrl,
        'QueueTimeOutURL' => $queueTimeoutUrl,
        'Remarks' => 'Check payment status',
        'Occasion' => 'INV-001',
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

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('apiUsername')
        ->once()
        ->andReturn('testapi');

    $client->shouldReceive('getSecurityCredential')
        ->once()
        ->andReturn('security-credential');

    $client->shouldReceive('getIdentifierType')
        ->once()
        ->with('till')
        ->andReturn(11);

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $client->shouldNotReceive('shortcode');

    $service = new TransactionStatusService($client);

    $response = $service->query(
        transactionId: 'RBX123456',
        resultUrl: $resultUrl,
        queueTimeoutUrl: $queueTimeoutUrl,
        identifierType: 'till',
        originalConversationId: 'AG_20260609_123456',
        remarks: 'Check payment status',
        occasion: 'INV-001',
        partyA: '600000',
    );

    expect($response)->toBe($expectedResponse);
});
