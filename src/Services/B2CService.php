<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;
use Carbon\Carbon;

class B2CService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    public function send(
        string $phoneNumber,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'B2C payment',
        string $occasion = '',
        string $commandId = 'BusinessPayment',
        ?string $shortCode = null
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($queueTimeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/b2c/v1/paymentrequest';

        $data = [
            'InitiatorName' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => $commandId,
            'Amount' => (int) ceil($amount),
            'PartyA' => $shortCode ?? $this->client->shortcode(),
            'PartyB' => $this->client->sanitizePhoneNumber($phoneNumber),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
            'Occasion' => $occasion,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('B2C Response Data', $result);
        }

        return $result;
    }

    public function sendToValidatedCustomer(
        string $msisdn,
        int|float $amount,
        string $resultUrl,
        string $timeoutUrl,
        string $remarks = 'B2C payment',
        string $ocassion = '',
        string $commandID = 'BusinessPayment',
        ?string $idNumber = null
    ): array {
        if (!$this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL');
        }

        if (!$this->client->isValidUrl($timeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL');
        }

        $url = $this->client->baseUrl() . '/mpesa/b2c/v1/paymentrequest';

        $data = [
            'apiUsername'               =>  $this->client->apiUsername(),
            'SecurityCredential'        =>  $this->client->getSecurityCredential(),
            'CommandID'                 =>  $commandID,
            'Amount'                    =>  floor($amount), // remove decimal points
            'PartyA'                    =>  $shortCode ?? $this->client->shortcode(),
            'PartyB'                    =>  $this->client->sanitizePhoneNumber($msisdn),
            'Remarks'                   =>  $remarks,
            'Occasion'                  =>  $ocassion, // Can be null
            'OriginatorConversationID'  =>  Carbon::rawParse('now')->format('YmdHis'), //unique id for the transaction
            'IDType'                    =>  '01', //01 for national id
            'IDNumber'                  =>  $idNumber,
            'QueueTimeOutURL'           =>  $timeoutUrl,
            'ResultURL'                 =>  $resultUrl,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('B2C Response Data', $result);
        }

        return $result;
    }
}
