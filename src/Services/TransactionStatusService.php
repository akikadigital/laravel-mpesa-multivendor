<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class TransactionStatusService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Query the status of a transaction.
     *
     * @param string $transactionId The ID of the transaction to query.
     * @param string $resultUrl The URL to receive the result of the query.
     * @param string $queueTimeoutUrl The URL to receive a timeout notification if the query takes too long.
     * @param string $identifierType The type of identifier used for PartyA (default: 'shortcode').
     * @param string $originalConversationId Unique identifier of the transaction returned in the response of the original transaction.
     * @param string $remarks Remarks for the transaction status query (default: 'Transaction status query').
     * @param string $occasion Occasion for the transaction status query (optional).
     * @param string|null $partyA The PartyA identifier (optional, defaults to client's shortcode).
     *
     * @return array The response from the M-Pesa API.
     *
     * @throws \InvalidArgumentException If the provided URLs are invalid.
     */
    public function query(
        string $transactionId,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $identifierType = 'shortcode',
        string $originalConversationId = '',
        string $remarks = 'Transaction status query',
        string $occasion = '',
        ?string $partyA = null
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($queueTimeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/transactionstatus/v1/query';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $transactionId,
            'PartyA' => $partyA ?? $this->client->shortcode(),
            'IdentifierType' => $this->client->getIdentifierType($identifierType),
            'OriginalConversationID'    =>  $originalConversationId,
            'ResultURL' => $resultUrl,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'Remarks' => $remarks,
            'Occasion' => $occasion,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
