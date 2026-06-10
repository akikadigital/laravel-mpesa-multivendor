<?php

namespace Akika\LaravelMpesaMultivendor\Support\Helpers;

trait MpesaHelper
{
    /**
     * Get the identifier type code for a given identifier type string.
     *
     * @param string $type
     * @return int
     */
    public function getIdentifierType(string $type): int
    {
        return match (strtolower($type)) {
            'msisdn' => 1,
            'tillnumber' => 2,
            'shortcode', 'paybill' => 4,
            default => throw new \InvalidArgumentException("Invalid identifier type [$type]"),
        };
    }

    /**
     * Get the Ratiba transaction type string for a given transaction type string.
     *
     * @param string $type
     * @return string
     */
    public function ratibaTransactionType(string $type): string
    {
        return match (strtolower($type)) {
            'paybill' => 'Standing Order Customer Pay Bill',
            'tillnumber' => 'Standing Order Customer Pay Merchant',
            default => throw new \InvalidArgumentException("Invalid Ratiba transaction type [$type]"),
        };
    }

    /**
     * Get the Ratiba frequency code for a given frequency string.
     *
     * @param string $frequency
     * @return int
     */
    public function ratibaFrequency(string $frequency): int
    {
        $result = match (strtolower($frequency)) {
            'one-off' => 1,
            'daily' => 2,
            'weekly' => 3,
            'monthly' => 4,
            'bi-monthly' => 5,
            'quarterly' => 6,
            'half-year' => 7,
            'yearly' => 8,
            default => throw new \InvalidArgumentException("Invalid frequency [$frequency]"),
        };

        return $result;
    }

    /**
     * Sanitize the phone number by getting rid of the leading 0 and replacing it with 254.
     *
     * @param string|null $phoneNumber
     * @return string|null
     */
    public function sanitizePhoneNumber(?string $phoneNumber): ?string
    {
        if (! $phoneNumber) {
            return null;
        }

        $phoneNumber = preg_replace('/\D+/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }

        if (str_starts_with($phoneNumber, '7') || str_starts_with($phoneNumber, '1')) {
            $phoneNumber = '254' . $phoneNumber;
        }

        if (! preg_match('/^254[17]\d{8}$/', $phoneNumber)) {
            throw new \InvalidArgumentException('Invalid Kenyan phone number.');
        }

        return $phoneNumber;
    }

    /**
     * 
     * Vaidate URL and if it fails, throw an exception with the submitted message
     */
    public function validateUrl(string $url, string $message): void
    {
        if (! $this->isValidUrl($url)) {
            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Validate that a given URL is a valid callback URL and does not contain any of the blacklisted keywords.
     *
     * @param string $url
     * @return bool
     */
    public function isValidUrl(string $url): bool
    {
        $url = strtolower($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return ! str_contains($url, 'mpesa')
            && ! str_contains($url, 'safaricom')
            && ! str_contains($url, 'daraja');
    }
}
