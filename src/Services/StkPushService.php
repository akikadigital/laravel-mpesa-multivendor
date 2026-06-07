<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class StkPushService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    public function push(
        string $phoneNumber,
        int|float $amount,
        string $callbackUrl,
        string $accountReference,
        string $transactionDesc = 'STK Push Payment',
        string $transactionType = 'CustomerPayBillOnline',
        ?string $partyB = null
    ): array {
        if (! $this->client->isValidUrl($callbackUrl)) {
            throw new \InvalidArgumentException('Invalid CallbackURL.');
        }

        $timestamp = now()->format('YmdHis');

        $url = $this->client->baseUrl() . '/mpesa/stkpush/v1/processrequest';

        $data = [
            'BusinessShortCode' => $this->client->shortcode(),
            'Password' => $this->client->generatePassword($timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => $transactionType,
            'Amount' => (int) ceil($amount),
            'PartyA' => $this->client->sanitizePhoneNumber($phoneNumber),
            'PartyB' => $partyB ?? $this->client->shortcode(),
            'PhoneNumber' => $this->client->sanitizePhoneNumber($phoneNumber),
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('STK Push Response Data', $result);
        }

        return $result;
    }

    public function query(string $checkoutRequestId): array
    {
        $timestamp = now()->format('YmdHis');

        $url = $this->client->baseUrl() . '/mpesa/stkpushquery/v1/query';

        $data = [
            'BusinessShortCode' => $this->client->shortcode(),
            'Password' => $this->client->generatePassword($timestamp),
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('STK Push Query Response Data', $result);
        }

        return $result;
    }
}
