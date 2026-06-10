<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;
use Carbon\Carbon;

class B2CService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Send money from your organization to a customer's mobile phone.
     *
     * @param string $phoneNumber The phone number of the customer (in international format, e.g., 2547XXXXXXXX).
     * @param int|float $amount The amount to be transacted.
     * @param string $resultUrl The URL to receive the transaction result notifications.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications if the transaction takes too long.
     * @param string $remarks Comments that are sent along with the transaction. Maximum 100 characters.
     * @param string $occasion A reference for the transaction, such as an invoice number or account number. Maximum 100 characters.
     * @param string $commandId The command ID for the transaction (default: 'BusinessPayment').
     * @return array The response from the API after initiating the transaction.
     * @throws \InvalidArgumentException If either of the provided URLs is invalid.
     */
    public function send(
        string $phoneNumber,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'B2C payment',
        string $occasion = '',
        string $commandId = 'BusinessPayment',
    ): array {

        $this->client->validateUrl($resultUrl, 'Invalid ResultURL.');
        $this->client->validateUrl($queueTimeoutUrl, 'Invalid QueueTimeOutURL.');

        $url = $this->client->baseUrl() . '/mpesa/b2c/v1/paymentrequest';

        $data = [
            'InitiatorName' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => $commandId,
            'Amount' => (int) ceil($amount),
            'PartyA' => $this->client->shortcode(),
            'PartyB' => $this->client->sanitizePhoneNumber($phoneNumber),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
            'Occasion' => $occasion,
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * This API enables you to transfer money from your organization to a customer’s mobile phone. The transaction moves money from the organization’s working account to the customer’s mobile money account.
     * @param $receiverShortCode - The shortcode of the organization that receives the transaction
     * @param $amount - The amount to be paid
     * @param $resultUrl - The endpoint that receives the response of the transaction
     * @param $timeoutUrl - The endpoint that receives timeout notifications
     * @param $remarks - Comments that are sent along with the transaction. Maximum 100 characters.
     * @param $accountReference - A reference for the transaction, such as an invoice number or account number. Maximum 100 characters.
     *
     * @return array The response from the API.
     *
     * @throws \InvalidArgumentException If the provided URLs are invalid.
     */
    public function topUp(
        string $receiverShortCode,
        int|float $amount,
        string $resultUrl,
        string $timeoutUrl,
        string $remarks = 'B2C TopUp',
        string $accountReference = '',
        ?string $requester = null
    ): array {

        $this->client->validateUrl($resultUrl, 'Invalid ResultURL.');
        $this->client->validateUrl($timeoutUrl, 'Invalid TimeOutURL.');

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/paymentrequest';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => 'BusinessPayToBulk',
            'SenderIdentifierType' => 4,
            'RecieverIdentifierType' => 4,
            'Amount' => floor($amount),
            'PartyA' => $this->client->shortcode(),
            'PartyB' => $receiverShortCode,
            'AccountReference' => $accountReference,
            'Requester' => $this->client->sanitizePhoneNumber($requester),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $timeoutUrl,
            'ResultURL' => $resultUrl,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
