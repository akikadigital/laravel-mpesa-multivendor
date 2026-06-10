<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class B2BService
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Send a B2B payment request to a business till number.
     *
     * @param string $receiverShortCode The shortcode of the receiving business.
     * @param int|float $amount The amount to be sent.
     * @param string $resultUrl The URL to receive the result of the transaction.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications.
     * @param string $remarks Remarks for the transaction.
     * @param string|null $requester Optional requester phone number (sanitized if provided).
     *
     * @return array The response from the M-Pesa API.
     *
     * @throws \InvalidArgumentException If the result URL or queue timeout URL is invalid.
     */

    public function buyGoods(
        string $receiverShortCode,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'B2B buy goods',
        ?string $requester = null
    ): array {
        return $this->paymentRequest(
            receiverShortCode: $receiverShortCode,
            amount: $amount,
            resultUrl: $resultUrl,
            queueTimeoutUrl: $queueTimeoutUrl,
            commandId: 'BusinessBuyGoods',
            receiverIdentifierType: 2,
            remarks: $remarks,
            accountReference: null,
            requester: $requester
        );
    }

    /**
     * Send a B2B payment request to pay bill account.
     *
     * @param string $receiverShortCode The shortcode of the receiving business.
     * @param int|float $amount The amount to be sent.
     * @param string $accountReference The account number to be associated with the payment. Up to 13 characters.
     * @param string $resultUrl The URL to receive the result of the transaction.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications.
     * @param string $remarks Remarks for the transaction.
     * @param string|null $requester Optional requester phone number (sanitized if provided).
     *
     * @return array The response from the M-Pesa API.
     *
     * @throws \InvalidArgumentException If the result URL or queue timeout URL is invalid.
     */
    public function paybill(
        string $receiverShortCode,
        int|float $amount,
        string $accountReference,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $remarks = 'B2B pay bill',
        ?string $requester = null
    ): array {
        return $this->paymentRequest(
            receiverShortCode: $receiverShortCode,
            amount: $amount,
            resultUrl: $resultUrl,
            queueTimeoutUrl: $queueTimeoutUrl,
            commandId: 'BusinessPayBill',
            receiverIdentifierType: 4,
            remarks: $remarks,
            accountReference: $accountReference,
            requester: $requester
        );
    }

    /**
     * This API enables you to transfer money from one company to another company within the same organization.
     * The transaction moves money from the organization’s working account to another organization’s utility account.
     * @param $destShortcode - The shortcode of the organization that receives the transaction
     * @param $partnerName - The name of the organization that receives the transaction
     * @param $amount - The amount to be paid
     * @param $paymentReference - The reference for the payment
     * @param $callbackUrl - The endpoint that receives the response of the transaction
     * @param $requestRefID - The unique reference for the transaction
     * 
     * @result - The result of the request: \Illuminate\Http\Client\Response
     */
    public function expressCheckout(
        string $partnerName,
        string $destShortcode,
        int|float $amount,
        string $paymentReference,
        string $callbackUrl,
        string $requestRefID
    ): array {
        $this->client->validateUrl($callbackUrl, 'Invalid CallbackURL.');

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/ussdpush/get-msisdn';

        return $this->client->makeRequest($url, [
            'primaryShortCode' => $this->client->shortcode(),
            'receiverShortCode' => $destShortcode,
            'partnerName' => $partnerName,
            'amount' => floor($amount),
            'paymentRef' => $paymentReference,
            'callbackUrl' => $callbackUrl,
            'RequestRefID' => $requestRefID,
        ]);
    }

    private function paymentRequest(
        string $receiverShortCode,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $commandId,
        int $receiverIdentifierType,
        string $remarks,
        ?string $accountReference = null,
        ?string $requester = null
    ): array {

        $this->client->validateUrl($resultUrl, 'Invalid ResultURL.');
        $this->client->validateUrl($queueTimeoutUrl, 'Invalid QueueTimeOutURL.');

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/paymentrequest';

        return $this->client->makeRequest($url, [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => $commandId,
            'SenderIdentifierType' => 4,
            'RecieverIdentifierType' => $receiverIdentifierType,
            'Amount' => (int) ceil($amount),
            'PartyA' => $this->client->shortcode(),
            'PartyB' => $receiverShortCode,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
            'AccountReference' => $accountReference,
            'Requester' => $requester !== null
                ? $this->client->sanitizePhoneNumber($requester)
                : null,
        ]);
    }
}