<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class RatibaService
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Create a standing order
     *
     * @param string $name The name of the standing order
     * @param string $startDate The start date of the standing order (Y-m-d)
     * @param string $endDate The end date of the standing order (Y-m-d)
     * @param string $transactionType The type of transaction (e.g., "CustomerPayBillOnline")
     * @param int|float $amount The amount to be transacted
     * @param string $phoneNumber The phone number of the recipient
     * @param string $callbackUrl The URL to receive callbacks
     * @param string $accountReference An account reference for the transaction
     * @param string $frequency The frequency of the standing order (e.g., "Daily", "Weekly", "Monthly")
     * @param string $transactionDesc A description for the transaction
     * @return array The response from the Mpesa API
     * @throws \InvalidArgumentException If the callback URL is invalid
     */
    public function createStandingOrder(
        string $name,
        string $startDate,
        string $endDate,
        string $transactionType,
        int|float $amount,
        string $phoneNumber,
        string $callbackUrl,
        string $accountReference,
        string $frequency,
        string $transactionDesc = 'Ratiba standing order'
    ): array {
        if (! $this->client->isValidUrl($callbackUrl)) {
            throw new \InvalidArgumentException('Invalid CallbackURL.');
        }

        $url = $this->client->baseUrl() . '/standingorder/v1/createStandingOrderExternal';

        $data = [
            'StandingOrderName' => $name,
            'StartDate' => date('Ymd', strtotime($startDate)),
            'EndDate' => date('Ymd', strtotime($endDate)),
            'BusinessShortCode' => $this->client->shortcode(),
            'TransactionType' => $this->client->ratibaTransactionType($transactionType),
            'ReceiverPartyIdentifierType' => $this->client->getIdentifierType($transactionType),
            'Amount' => floor($amount),
            'PartyA' => $this->client->sanitizePhoneNumber($phoneNumber),
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
            'Frequency' => $this->client->ratibaFrequency($frequency),
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Query the status of a standing order
     *
     * @param string $standingOrderId The ID of the standing order to query
     * @return array The response from the Mpesa API
     */
    public function query(
        string $standingOrderId
    ): array {
        $url = $this->client->baseUrl() . '/standingorder/v1/queryStandingOrder';

        $data = [
            'StandingOrderID' => $standingOrderId,
            'BusinessShortCode' => $this->client->shortcode(),
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Cancel a standing order
     *
     * @param string $standingOrderId The ID of the standing order to cancel
     * @param string $callbackUrl The URL to receive callbacks
     * @return array The response from the Mpesa API
     * @throws \InvalidArgumentException If the callback URL is invalid
     */
    public function cancel(
        string $standingOrderId,
        string $callbackUrl,
    ): array {
        if (! $this->client->isValidUrl($callbackUrl)) {
            throw new \InvalidArgumentException('Invalid CallbackURL.');
        }

        $url = $this->client->baseUrl() . '/standingorder/v1/cancelStandingOrderExternal';

        $data = [
            'StandingOrderID' => $standingOrderId,
            'BusinessShortCode' => $this->client->shortcode(),
            'CallBackURL' => $callbackUrl,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
