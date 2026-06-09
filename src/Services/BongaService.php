<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class BongaService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Calculate Bonga points for a customer.
     *
     * @param int $points The number of points to calculate.
     * @return array The response from the API.
     */
    public function calculate(
        int $points
    ): array {
        $url = $this->client->baseUrl() . '/v1/lipa/na/bonga/calculate-points';

        $data = [
            'points' => $points,
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Redeem Bonga points for a customer.
     *
     * @param string $phoneNumber The customer's phone number.
     * @param float $amount The amount to redeem in currency units.
     * @param string $transactionReference A unique identifier for the transaction, such as an order ID or user ID.
     * @return array The response from the API.
     */
    public function pay(
        string $phoneNumber,
        float $amount,
        string $transactionReference,
        float $conversionRate = 0.2
    ): array {
        $url = $this->client->baseUrl() . '/v1/lipa/na/bonga/redeem-paybill';

        $data = [
            "msisdn" => $this->client->sanitizePhoneNumber($phoneNumber),
            "amount" => $amount,
            "bongaPoints" => (int) ceil($amount * $conversionRate),
            "conversionRate" => $conversionRate,
            "shortCode" => $this->client->shortcode(),
            "accountNumber" => $transactionReference,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
