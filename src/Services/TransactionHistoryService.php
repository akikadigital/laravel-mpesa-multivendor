<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class TransactionHistoryService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Register a URL to receive transaction history callbacks.
     *
     * @param string $nominatedNumber The phone number to receive the transaction history callbacks (in international format, e.g., 2547XXXXXXXX).
     * @param string $callbackUrl The URL to receive the transaction history callbacks.
     * @return array The response from the API after registering the callback URL.
     * @throws \InvalidArgumentException If the provided callback URL is invalid.
     */
    public function register(
        string $nominatedNumber,
        string $callbackUrl
    ): array {
        if (! $this->client->isValidUrl($callbackUrl)) {
            throw new \InvalidArgumentException('Invalid CallbackURL.');
        }

        $url = $this->client->baseUrl() . '/pulltransactions/v1/register';

        $data = [
            'ShortCode' => $this->client->shortcode(),
            'ResponseType' => 'Pull',
            'NominatedNumber' => $this->client->sanitizePhoneNumber($nominatedNumber),
            'CallBackURL' => $callbackUrl,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Transaction History Response Data', $result);
        }

        return $result;
    }

    /**
     * Query transaction history within a specified date range.
     *
     * @param string $startDate The start date for the query (in 'Y-m-d H:i:s' format).
     * @param string $endDate The end date for the query (in 'Y-m-d H:i:s' format).
     * @param int $offset The offset value for pagination (default: 0).
     *
     * @return array The response from the API containing transaction history.
     *
     * @throws \InvalidArgumentException If the provided dates are invalid.
     */
    public function query(
        string $startDate,
        string $endDate,
        int $offset = 0
    ): array {

        $url = $this->client->baseUrl() . '/pulltransactions/v1/query';

        $data = [
            'ShortCode' => $this->client->shortcode(),
            'StartDate' => date('Y-m-d H:i:s', strtotime($startDate)),
            'EndDate' => date('Y-m-d H:i:s', strtotime($endDate)),
            'OffSetValue' => $offset,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Transaction History Response Data', $result);
        }

        return $result;
    }
}
