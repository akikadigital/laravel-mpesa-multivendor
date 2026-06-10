<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class ReversalService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Reverse a transaction, at the momet, only C2B reversals are supported.
     *
     * @param string $transactionId The ID of the transaction to reverse.
     * @param int|float $amount The amount to reverse.
     * @param string $resultUrl The URL to receive the reversal result.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications.
     * @param string $remarks Optional remarks for the reversal (default: 'Transaction reversal').
     * @param string|null $receiverParty Optional receiver party (defaults to client's shortcode).
     * @return array The response from the Mpesa API.
     * @throws \InvalidArgumentException If any of the provided URLs are invalid.
     */
    public function reverse(
        string $transactionId,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'Transaction reversal',
        ?string $receiverParty = null,
    ): array {

        $this->client->validateUrl($resultUrl, 'Invalid ResultURL.');
        $this->client->validateUrl($queueTimeoutUrl, 'Invalid QueueTimeOutURL.');

        $url = $this->client->baseUrl() . '/mpesa/reversal/v1/request';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => 'TransactionReversal',
            'TransactionID' => $transactionId,
            'Amount' => (int) ceil($amount),
            'ReceiverParty' => $receiverParty ?? $this->client->shortcode(),
            'RecieverIdentifierType' => '11',
            'Remarks' => $remarks,
            'ResultURL' => $resultUrl,
            'QueueTimeOutURL' => $queueTimeoutUrl,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
