<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class C2BService
{
    public function __construct(protected MpesaClient $client) {}

    public function registerUrls(
        string $confirmationUrl,
        string $validationUrl,
        string $responseType = 'Completed',
        ?string $shortCode = null
    ): array {
        if (! $this->client->isValidUrl($confirmationUrl)) {
            throw new \InvalidArgumentException('Invalid ConfirmationURL.');
        }

        if (! $this->client->isValidUrl($validationUrl)) {
            throw new \InvalidArgumentException('Invalid ValidationURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/c2b/v1/registerurl';

        $data = [
            'ShortCode' => $shortCode ?? $this->client->shortcode(),
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

    public function simulate(
        string $phoneNumber,
        int|float $amount,
        string $billRefNumber,
        string $commandId = 'CustomerPayBillOnline',
        ?string $shortCode = null
    ): array {
        $url = $this->client->baseUrl() . '/mpesa/c2b/v1/simulate';

        $data = [
            'ShortCode' => $shortCode ?? $this->client->shortcode(),
            'CommandID' => $commandId,
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
