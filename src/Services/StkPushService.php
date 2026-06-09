<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class StkPushService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Initiate an STK Push payment request.
     *
     * @param string $phoneNumber The customer's phone number (in international format, e.g., 2547XXXXXXXX).
     * @param int|float $amount The amount to be paid.
     * @param string $callbackUrl The URL to receive the payment result callback.
     * @param string $accountReference An account reference for the transaction (e.g., invoice number).
     * @param string $receivingShortCode Shortcode to receive funds, can be null
     * @param string $transactionDesc A description for the transaction (optional).
     * @param string $transactionType The type of transaction (default: 'CustomerPayBillOnline').
     * @return array The response from the Mpesa API.
     * @throws \InvalidArgumentException If the callback URL is invalid.
     */
    public function push(
        string $phoneNumber,
        int|float $amount,
        string $callbackUrl,
        string $accountReference,
        string $transactionDesc = 'STK Push Payment',
        string $transactionType = 'CustomerPayBillOnline',
        ?string $receivingShortCode = null,
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
            'PartyB' => $receivingShortCode ?? $this->client->shortcode(),
            'PhoneNumber' => $this->client->sanitizePhoneNumber($phoneNumber),
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Query the status of an STK Push payment.
     *
     * @param string $checkoutRequestId The checkout request ID to query.
     * @return array The response from the Mpesa API.
     */
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

        return $this->client->makeRequest($url, $data);
    }
}
