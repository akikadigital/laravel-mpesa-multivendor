<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class PochiService
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Get the account balance for the shortcode
     *
     * @return array The response from the Mpesa API
     */
    public function send(
        string $transctionId,
        string $msisdn,
        int|float $amount,
        string $resultUrl,
        string $timeoutUrl,
        string $remarks = 'B2P Pochi payment',
        string $ocassion = '',
        ?string $shortCode = null
    ): array
    {
        $url = $this->client->baseUrl() . '/pochiv1/accountbalance';

        $data = [
            "OriginatorConversationID" => $transctionId,
            "InitiatorName" => $this->client->apiUsername(),
            "SecurityCredential" => $this->client->getSecurityCredential(),
            "CommandID" => "BusinessPayToPochi",
            "Amount" => $amount,
            "PartyA" => $shortCode ?? $this->client->shortcode(),
            "PartyB" => $this->client->sanitizePhoneNumber($msisdn),
            "Remarks" => $remarks,
            "QueueTimeOutURL" => $timeoutUrl,
            "ResultURL" => $resultUrl,
            "Occassion" => $ocassion,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('B2C Response Data', $result);
        }

        return $result;
    }
}