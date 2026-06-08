<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class AccountBalanceService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Check the account balance of the shortcode.
     *
     * @param string $resultUrl The URL to receive the result of the account balance request.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications if the request takes too long.
     * @param string $identifierType The type of identifier (default: 'shortcode').
     * @param string $remarks Optional remarks for the account balance request.
     *
     * @return array The response from the API.
     */
    public function check(
        string $resultUrl,
        string $queueTimeoutUrl,
        string $identifierType = 'shortcode',
        string $remarks = 'Account balance request',
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($queueTimeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/accountbalance/v1/query';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => 'AccountBalance',
            'PartyA' => $this->client->shortcode(),
            'IdentifierType' => $this->client->getIdentifierType($identifierType),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Account Balance Response Data', $result);
        }

        return $result;
    }
}
