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

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Bonga Request Response Data', $result);
        }

        return $result;
    }

    /**
     * Redeem Bonga points for a customer.
     *
     * @param string $phoneNumber The customer's phone number.
     * @param float $amount The amount to redeem in currency units.
     * @return array The response from the API.
     */
    public function pay(
        string $phoneNumber,
        float $amount
    ): array {
        $url = $this->client->baseUrl() . '/v1/lipa/na/bonga/redeem-paybill';

        $data = [
            "msisdn" => $this->client->sanitizePhoneNumber($phoneNumber),
            "amount" => $amount,
            "bongaPoints" => (int) ceil($amount * 0.2), // Assuming a conversion rate of 0.2 points per unit of currency
            "conversionRate" => 0.2, // This should ideally come from the API or be configurable
            "shortCode" => $this->client->shortcode(),
            "accountNumber" => "test", // This should ideally be a unique identifier for the transaction, such as an order ID or user ID
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Bonga Redeem Response Data', $result);
        }

        return $result;
    }
}
