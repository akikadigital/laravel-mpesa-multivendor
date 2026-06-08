<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class ReversalService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Reverse a transaction.
     *
     * @param string $transactionId The ID of the transaction to reverse.
     * @param int|float $amount The amount to reverse.
     * @param string $resultUrl The URL to receive the reversal result.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications.
     * @param string $remarks Optional remarks for the reversal (default: 'Transaction reversal').
     * @param string $occasion Optional occasion for the reversal (default: '').
     * @param string|null $receiverParty Optional receiver party (defaults to client's shortcode).
     * @param string $receiverIdentifierType The identifier type for the receiver party (default: 'shortcode').
     * @return array The response from the Mpesa API.
     * @throws \InvalidArgumentException If any of the provided URLs are invalid.
     */
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
