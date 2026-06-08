<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class TaxRemittanceService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Remit tax to KRA using the B2B API.
     *
     * @param int|float $amount The amount to remit.
     * @param string $accountReference The payment registration number (PRN) issued by KRA.
     * @param string $resultUrl The URL to receive the result of the transaction.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications.
     * @param string $remarks Optional remarks for the transaction (default: 'Tax remittance').
     * @param string $commandId Optional command ID (default: 'PayTaxToKRA').
     * @param string|null $partyA Optional sender shortcode or phone number (defaults to configured shortcode).
     *
     * @return array The response from the API.
     *
     * @throws \InvalidArgumentException If the provided URLs are invalid.
     */
    public function remit(
        int|float $amount,
        string $accountReference,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'Tax remittance',
        string $commandId = 'PayTaxToKRA',
        ?string $partyA = null,
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($queueTimeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/remittax';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => $commandId,
            'SenderIdentifierType' => 4,
            'RecieverIdentifierType' => 4,
            'Amount' => (int) ceil($amount),
            'PartyA' => $partyA ?? $this->client->shortcode(),
            'PartyB' => '572572', // KRA Paybill number
            'AccountReference' => $accountReference,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Tax Remittance Response Data', $result);
        }

        return $result;
    }
}
