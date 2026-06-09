<?php

use Akika\LaravelMpesaMultivendor\Services\OrganizationServce;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

afterEach(function () {
    \Mockery::close();
});

it('retrieves organization details successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/sfcverify/v1/query/info';

    $expectedData = [
        'IdentifierType' => 4,
        'Identifier' => '174379',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
        'OrganizationName' => 'Test Organization',
        'ShortCode' => '174379',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new OrganizationServce($client);

    $response = $service->getOrganizationDetails('174379');

    expect($response)->toBe($expectedResponse);
});

it('passes the provided shortcode to the api request', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/sfcverify/v1/query/info';

    $expectedData = [
        'IdentifierType' => 4,
        'Identifier' => '600000',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new OrganizationServce($client);

    $response = $service->getOrganizationDetails('600000');

    expect($response)->toBe($expectedResponse);
});

it('returns organization api errors unchanged', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/sfcverify/v1/query/info';

    $expectedResponse = [
        'ResponseCode' => '500',
        'ResponseDescription' => 'Internal Server Error',
    ];

    $client->shouldReceive('baseUrl')
        ->once()
        ->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, [
            'IdentifierType' => 4,
            'Identifier' => '174379',
        ])
        ->andReturn($expectedResponse);

    $service = new OrganizationServce($client);

    $response = $service->getOrganizationDetails('174379');

    expect($response)->toBe($expectedResponse);
});
