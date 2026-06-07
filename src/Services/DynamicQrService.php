<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class DynamicQrService
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Generate a dynamic QR code for M-Pesa payments.
     *
     * @param string $merchantName The name of the merchant.
     * @param string $reference A unique reference for the transaction.
     * @param int|float $amount The amount to be paid.
     * @param string $transactionCode The transaction code (default: 'PB').
     * @param string|null $creditPartyIdentifier Optional credit party identifier (defaults to shortcode).
     * @param int $size The size of the QR code (default: 300).
     * @return array The response from the M-Pesa API containing the QR code details.
     */
    public function generate(
        string $merchantName,
        string $reference,
        int|float $amount,
        string $transactionCode = 'PB',
        ?string $creditPartyIdentifier = null,
        int $size = 300
    ): array {
        $url = $this->client->baseUrl() . '/mpesa/qrcode/v1/generate';

        $data = [
            'MerchantName' => $merchantName,
            'RefNo' => $reference,
            'Amount' => (int) ceil($amount),
            'TrxCode' => $transactionCode,
            'CPI' => $creditPartyIdentifier ?? $this->client->shortcode(),
            'Size' => $size,
        ];

        return $this->client->makeRequest($url, $data);
    }
}
