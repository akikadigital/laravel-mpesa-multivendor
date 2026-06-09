<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class ImsiService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Get the IMSI number for a given phone number.
     * NOTE: This API is charged per request, so use it only when necessary (e.g., for fraud prevention or network analysis).
     *
     * @param string $phoneNumber The phone number to query (in international format, e.g., 2547XXXXXXXX).
     * @return array The response from the API containing the IMSI number.
     */
    public function query(string $phoneNumber): array
    {
        $url = $this->client->baseUrl() . '/imsi/v1/checkATI';

        $data = [
            'customerNumber' => $this->client->sanitizePhoneNumber($phoneNumber),
        ];

        return $this->client->makeRequest($url, $data);
    }
}