<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class B2BService
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Send a B2B payment request.
     *
     * @param bool $toPaybill Whether the payment is to a Paybill (true) or BuyGoods (false) account.
     * @param string $receiverShortCode The shortcode of the receiving business.
     * @param int|float $amount The amount to be sent.
     * @param string $resultUrl The URL to receive the result of the transaction.
     * @param string $queueTimeoutUrl The URL to receive timeout notifications.
     * @param string $receiverIdentifierType The type of identifier for the receiver (shortcode, msisdn, or tillnumber).
     * @param string $remarks Remarks for the transaction.
     * @param string $accountReference Account reference for the transaction.
     * @param string|null $senderShortCode Optional shortcode of the sending business (defaults to client's shortcode).
     * @param string|null $initiator Optional initiator name (defaults to client's API username).
     * @param string|null $requester Optional requester phone number (sanitized if provided).
     *
     * @return array The response from the M-Pesa API.
     *
     * @throws \InvalidArgumentException If the result URL or queue timeout URL is invalid.
     */
    
    public function send(
        bool $toPaybill,
        string $receiverShortCode,
        int|float $amount,
        string $resultUrl,
        string $queueTimeoutUrl,
        string $receiverIdentifierType = 'shortcode',
        string $remarks = 'B2B payment',
        string $accountReference = '',
        ?string $senderShortCode = null,
        ?string $initiator = null,
        ?string $requester = null
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($queueTimeoutUrl)) {
            throw new \InvalidArgumentException('Invalid QueueTimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/paymentrequest';

        $data = [
            'Initiator' => $initiator ?? $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => $toPaybill ? 'BusinessPayBill' : 'BusinessBuyGoods',
            'SenderIdentifierType' => 4,
            'RecieverIdentifierType' => $this->client->getIdentifierType($receiverIdentifierType),
            'Amount' => (int) ceil($amount),
            'PartyA' => $senderShortCode ?? $this->client->shortcode(),
            'PartyB' => $receiverShortCode,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $queueTimeoutUrl,
            'ResultURL' => $resultUrl,
            'AccountReference' => $accountReference,
            'Requester' =>  $requester !== null ? $this->client->sanitizePhoneNumber($requester) : null, // Optional. The consumer’s mobile number on behalf of whom you are paying.
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('B2B Response Data', $result);
        }

        return $result;
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
        if (! $this->client->isValidUrl($callbackUrl)) {
            throw new \InvalidArgumentException('Invalid callback Url');
        }

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/ussdpush/get-msisdn';

        $data = [
            'primaryShortCode' => $this->client->shortcode(),
            'receiverShortCode' => $destShortcode,
            'partnerName' => $partnerName,
            'amount' => floor($amount),
            'paymentRef' => $paymentReference,
            'callbackUrl' => $callbackUrl,
            'RequestRefID' => $requestRefID
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('B2B Express Checkout Data: ' . json_encode($data));
            info('B2B Express Checkout Response Data: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * This API enables you to transfer money from your organization to a customer’s mobile phone. The transaction moves money from the organization’s working account to the customer’s mobile money account.
     * @param $receiverShortCode - The shortcode of the organization that receives the transaction
     * @param $amount - The amount to be paid
     * @param $resultUrl - The endpoint that receives the response of the transaction
     * @param $timeoutUrl - The endpoint that receives timeout notifications
     * @param $remarks - Comments that are sent along with the transaction. Maximum 100 characters.
     * @param $accountReference - A reference for the transaction, such as an invoice number or account number. Maximum 100 characters.
     * @param $shortCode - The shortcode of the organization sending the payment. Defaults to the configured shortcode.
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
        ?string $shortCode = null,
        ?string $requester = null
    ): array {
        if (! $this->client->isValidUrl($resultUrl)) {
            throw new \InvalidArgumentException('Invalid ResultURL.');
        }

        if (! $this->client->isValidUrl($timeoutUrl)) {
            throw new \InvalidArgumentException('Invalid TimeOutURL.');
        }

        $url = $this->client->baseUrl() . '/mpesa/b2b/v1/paymentrequest';

        $data = [
            'Initiator' => $this->client->apiUsername(),
            'SecurityCredential' => $this->client->getSecurityCredential(),
            'CommandID' => 'BusinessPayToBulk',
            'SenderIdentifierType' => 4,
            'RecieverIdentifierType' => 4,
            'Amount' => floor($amount),
            'PartyA' => $shortCode ?? $this->client->shortcode(),
            'PartyB' => $receiverShortCode,
            'AccountReference' => $accountReference,
            'Requester' => $this->client->sanitizePhoneNumber($requester),
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $timeoutUrl,
            'ResultURL' => $resultUrl,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('B2C TopUp Response Data', $result);
        }

        return $result;
    }
}
