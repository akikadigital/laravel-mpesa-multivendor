<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class AccountBalanceService
{
    public function __construct(protected MpesaClient $client) {}

    public function check(
        string $resultUrl,
        string $queueTimeoutUrl,
        string $identifierType = 'shortcode',
        string $remarks = 'Account balance request',
        ?string $shortCode = null
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
            'PartyA' => $shortCode ?? $this->client->shortcode(),
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
