<?php

use Akika\LaravelMpesaMultivendor\Services\ReversalService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('reverses a transaction successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/reversal/result';
    $queueTimeoutUrl = 'https://example.com/mpesa/reversal/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';

    $expectedData = [
        'Initiator' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'TransactionReversal',
        'TransactionID' => 'RBX123456',
        'Amount' => 101,
        'ReceiverParty' => '174379',
        'RecieverIdentifierType' => '11',
        'Remarks' => 'Transaction reversal',
        'ResultURL' => $resultUrl,
        'QueueTimeOutURL' => $queueTimeoutUrl,
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

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new ReversalService($client);

    $response = $service->reverse(
        transactionId: 'RBX123456',
        amount: 100.50,
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

    $service = new ReversalService($client);

    $service->reverse(
        transactionId: 'RBX123456',
        amount: 100,
        resultUrl: 'invalid-result-url',
        queueTimeoutUrl: 'https://example.com/mpesa/reversal/timeout',
    );
})->throws(InvalidArgumentException::class, 'Invalid ResultURL.');

it('throws an exception when queue timeout url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/reversal/result';

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with($resultUrl)
        ->andReturnTrue();

    $client->shouldReceive('isValidUrl')
        ->once()
        ->with('invalid-timeout-url')
        ->andReturnFalse();

    $service = new ReversalService($client);

    $service->reverse(
        transactionId: 'RBX123456',
        amount: 100,
        resultUrl: $resultUrl,
        queueTimeoutUrl: 'invalid-timeout-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid QueueTimeOutURL.');

it('uses custom receiver party, identifier type, remarks and occasion', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $resultUrl = 'https://example.com/mpesa/reversal/result';
    $queueTimeoutUrl = 'https://example.com/mpesa/reversal/timeout';

    $expectedUrl = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';

    $expectedData = [
        'Initiator' => 'testapi',
        'SecurityCredential' => 'security-credential',
        'CommandID' => 'TransactionReversal',
        'TransactionID' => 'RBX123456',
        'Amount' => 250,
        'ReceiverParty' => '600000',
        'RecieverIdentifierType' => '11',
        'Remarks' => 'Reverse duplicated payment',
        'ResultURL' => $resultUrl,
        'QueueTimeOutURL' => $queueTimeoutUrl,
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

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $client->shouldNotReceive('shortcode');

    $service = new ReversalService($client);

    $response = $service->reverse(
        transactionId: 'RBX123456',
        amount: 250,
        resultUrl: $resultUrl,
        queueTimeoutUrl: $queueTimeoutUrl,
        remarks: 'Reverse duplicated payment',
        receiverParty: '600000'
    );

    expect($response)->toBe($expectedResponse);
});
