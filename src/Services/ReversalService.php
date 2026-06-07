<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class ReversalService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    public function reverse(
        string $transactionId,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'Transaction reversal',
        string $occasion = '',
        ?string $receiverParty = null,
        string $receiverIdentifierType = 'shortcode'
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($queueTimeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/reversal/v1/request';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => 'TransactionReversal',
            'TransactionID' => $transactionId,
            'Amount' => (int) ceil($amount),
            'ReceiverParty' => $receiverParty ?? $this->client->shortcode(),
            'ReceiverIdentifierType' => $this->client->getIdentifierType($receiverIdentifierType),
            'Remarks' => $remarks,
            'Occasion' => $occasion,
            'ResultURL' => $resultUrl,
            'QueueTimeOutURL' => $queueTimeoutUrl,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
