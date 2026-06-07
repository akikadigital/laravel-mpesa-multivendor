<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class C2BService
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Register the Confirmation and Validation URLs for C2B transactions.
     *
     * @param string $confirmationUrl The URL to receive payment confirmation notifications.
     * @param string $validationUrl The URL to receive payment validation requests.
     * @param string $responseType Either Cancelled or Completed (default: 'Completed').
     * @return array The response from the API after registering the URLs.
     * @throws \InvalidArgumentException If either of the provided URLs is invalid.
     */
    public function registerUrls(
        string $confirmationUrl,
        string $validationUrl,
        string $responseType = 'Completed'
    ): array {
        if (! $this->client->isValidUrl($confirmationUrl)) {
            throw new \InvalidArgumentException('Invalid ConfirmationURL.');
        }

        if (! $this->client->isValidUrl($validationUrl)) {
            throw new \InvalidArgumentException('Invalid ValidationURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/c2b/v1/registerurl';

        $data = [
            'ShortCode' => $this->client->shortcode(),
            'ResponseType' => $responseType,
            'ConfirmationURL' => $confirmationUrl,
            'ValidationURL' => $validationUrl,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('C2B Register URL Response Data', $result);
        }

        return $result;
    }

    /**
     * Simulate a C2B transaction (for testing purposes).
     *
     * @param string $phoneNumber The phone number of the customer (in international format, e.g., 2547XXXXXXXX).
     * @param int|float $amount The amount to be transacted.
     * @param string $billRefNumber A reference number for the bill (e.g., invoice number).
     * @return array The response from the API after simulating the transaction.
     */
    public function simulate(
        string $phoneNumber,
        int|float $amount,
        ?string $billRefNumber = null,
    ): array {
        $url = $this->client->baseUrl() . '/mpesa/c2b/v1/simulate';

        $data = [
            'ShortCode' => $this->client->shortcode(),
            'CommandID' => $billRefNumber == null ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
            'Amount' => (int) ceil($amount),
            'Msisdn' => $this->client->sanitizePhoneNumber($phoneNumber),
            'BillRefNumber' => $billRefNumber,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('C2B Simulate Response Data', $result);
        }

        return $result;
    }
}
