<?php

use Akika\LaravelMpesaMultivendor\Services\BillManagerService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

uses()->group('services', 'bill-manager');

afterEach(function () {
    \Mockery::close();
});

it('opts in shortcode for bill manager successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $callbackUrl = 'https://example.com/bill-manager/callback';
    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/billmanager-invoice/optin';

    $expectedData = [
        'shortcode' => '174379',
        'email' => 'billing@example.com',
        'officialContact' => '254712345678',
        'sendReminders' => '1',
        'logo' => 'https://example.com/logo.png',
        'callbackurl' => $callbackUrl,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success',
    ];

    $client->shouldReceive('validateUrl')
        ->once()
        ->with($callbackUrl, 'Invalid CallbackURL.')
        ->andReturnNull();

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

    $service = new BillManagerService($client);

    $response = $service->optIn(
        email: 'billing@example.com',
        officialContact: '0712345678',
        sendReminders: '1',
        logo: 'https://example.com/logo.png',
        callbackUrl: $callbackUrl,
    );

    expect($response)->toBe($expectedResponse);
});

it('throws an exception when bill manager callback url is invalid', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $client->shouldReceive('validateUrl')
        ->once()
        ->with('invalid-callback-url', 'Invalid CallbackURL.')
        ->andThrow(new InvalidArgumentException('Invalid CallbackURL.'));

    $service = new BillManagerService($client);

    $service->optIn(
        email: 'billing@example.com',
        officialContact: '0712345678',
        callbackUrl: 'invalid-callback-url',
    );
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');

it('creates a single invoice successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/billmanager-invoice/single-invoicing';

    $items = [
        [
            'itemName' => 'Delivery Fee',
            'amount' => 50,
        ],
    ];

    $expectedData = [
        'externalReference' => 'INV-001',
        'billedFullName' => 'John Doe',
        'billedPhoneNumber' => '254712345678',
        'billedPeriod' => 'June 2026',
        'invoiceName' => 'Water Bill',
        'dueDate' => '2026-06-30',
        'accountReference' => 'ACC-001',
        'amount' => 101,
        'invoiceItems' => $items,
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Invoice created successfully',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BillManagerService($client);

    $response = $service->singleInvoice(
        externalReference: 'INV-001',
        billedFullName: 'John Doe',
        billedPhoneNumber: '0712345678',
        invoiceName: 'Water Bill',
        amount: 100.50,
        dueDate: '2026-06-30',
        accountReference: 'ACC-001',
        billingPeriod: 'June 2026',
        items: $items,
    );

    expect($response)->toBe($expectedResponse);
});

it('creates bulk invoices successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/billmanager-invoice/bulk-invoicing';

    $invoices = [
        [
            'externalReference' => 'INV-001',
            'billedFullName' => 'John Doe',
            'billedPhoneNumber' => '254712345678',
            'billedPeriod' => 'June 2026',
            'invoiceName' => 'Water Bill',
            'dueDate' => '2026-06-30',
            'accountReference' => 'ACC-001',
            'amount' => 100,
            'invoiceItems' => [],
        ],
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Bulk invoices created successfully',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $invoices)
        ->andReturn($expectedResponse);

    $service = new BillManagerService($client);

    $response = $service->bulkInvoice($invoices);

    expect($response)->toBe($expectedResponse);
});

it('cancels a single invoice successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/billmanager-invoice/cancel-single-invoice';

    $expectedData = [
        'externalReference' => 'INV-001',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Invoice cancelled successfully',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BillManagerService($client);

    $response = $service->cancelSingleInvoice('INV-001');

    expect($response)->toBe($expectedResponse);
});

it('cancels bulk invoices successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/billmanager-invoice/cancel-bulk-invoice';

    $externalReferences = [
        'INV-001',
        'INV-002',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Bulk invoices cancelled successfully',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $externalReferences)
        ->andReturn($expectedResponse);

    $service = new BillManagerService($client);

    $response = $service->cancelBulkInvoices($externalReferences);

    expect($response)->toBe($expectedResponse);
});

it('reconciles a bill manager payment successfully', function () {
    $client = \Mockery::mock(MpesaClient::class);

    $expectedUrl = 'https://sandbox.safaricom.co.ke/v1/billmanager-invoice/reconciliation';

    $expectedData = [
        'transactionId' => 'RBX123456',
        'paidAmount' => 500.75,
        'msisdn' => '254712345678',
        'dateCreated' => '2026-06-09 10:30:45',
        'accountReference' => 'ACC-001',
        'shortCode' => '174379',
    ];

    $expectedResponse = [
        'ResponseCode' => '0',
        'ResponseDescription' => 'Payment reconciled successfully',
    ];

    $client->shouldReceive('baseUrl')->once()->andReturn('https://sandbox.safaricom.co.ke');

    $client->shouldReceive('sanitizePhoneNumber')
        ->once()
        ->with('0712345678')
        ->andReturn('254712345678');

    $client->shouldReceive('shortcode')->once()->andReturn('174379');

    $client->shouldReceive('makeRequest')
        ->once()
        ->with($expectedUrl, $expectedData)
        ->andReturn($expectedResponse);

    $service = new BillManagerService($client);

    $response = $service->reconciliation(
        transactionId: 'RBX123456',
        amount: 500.75,
        msisdn: '0712345678',
        dateCreated: '2026-06-09 10:30:45',
        accountReference: 'ACC-001',
    );

    expect($response)->toBe($expectedResponse);
});
