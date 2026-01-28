<?php

namespace Akika\LaravelMpesaMultivendor\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

trait MpesaTrait
{
    // --------------------------------- Token Generation ---------------------------------

    /**
     *   Fetch the token from the database if it exists and is not expired
     *   If it does not exist or is expired, generate a new token and save it to the database
     */

    function getToken(): string
    {
        $url = $this->url . '/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Failed to fetch M-Pesa access token: ' . $response->body()
            );
        }

        return $response->json('access_token');
    }

    function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $this->accessToken = $this->getToken();

        if ($this->debugMode) {
            info('Mpesa access token generated');
        }

        return $this->accessToken;
    }

    /**
     *   Make a request to the Mpesa API
     */

    function makeRequest(string $url, array $body): ?string
    {
        if ($this->debugMode) {
            info('Invoked URL: ' . $url);
            info('Request Body', $body);
            info('Mpesa access token retrieved');
        }

        $response = Http::withToken($this->getAccessToken())
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->post($url, $body); // Laravel automatically JSON-encodes arrays

        if (! $response->successful()) {
            logger()->error('Daraja HTTP request failed', [
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null; // or throw
        }

        return $response->body(); // string (JSON)
    }

    /**
     *   Get the identifier type given the type
     */

    function getIdentifierType(string $type): int
    {
        return match (strtolower($type)) {
            'msisdn'     => 1,
            'tillnumber' => 2,
            'shortcode',
            'paybill'    => 4,
            default      => throw new \InvalidArgumentException("Invalid identifier type [$type]"),
        };
    }

    /**
     *   Get the transaction type given the type
     */

    function ratibaTransactionType($type): string
    {
        $types = [
            'paybill' => 'Standing Order Customer Pay Bill',
            'tillnumber' => 'Standing Order Customer Pay Marchant',
        ];

        return $types[$type];
    }

    /**
     *   Get the frequency given the frequency
     */

    function ratibaFrequency(string $frequency): int
    {
        $frequencies = [
            'one-off' => 1,
            'daily' => 2,
            'weekly' => 3,
            'monthly' => 4,
            'bi-monthly' => 5,
            'quarterly' => 6,
            'half-year' => 7,
            'yearly' => 8,
        ];

        if (! isset($frequencies[$frequency])) {
            throw new \InvalidArgumentException("Invalid frequency [$frequency]");
        }

        return $frequencies[$frequency];
    }

    /**
     *   Generate the password for the STK push
     */

    function generatePassword($timestamp): string
    {
        $password = base64_encode($this->mpesaShortCode . $this->passKey . $timestamp);
        if ($this->debugMode) {
            info('Stamp: ' . $this->mpesaShortCode);
            info('Passkey: ' . $this->passKey);
            info('Time: ' . $timestamp);
            info('Generated Password: ' . $password);
        }

        return $password;
    }

    /**
     *   Generate the certificate for the API
     */

    function generateCertificate(): string
    {
        if (config('mpesa.env') == 'sandbox') {
            $publicKey = File::get(__DIR__ . '/../../certificates/SandboxCertificate.cer');
        } else {
            $publicKey = File::get(__DIR__ . '/../../certificates/ProductionCertificate.cer');
        }
        openssl_public_encrypt($this->apiPassword, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    /**
     *   Sanitize the phone number by getting rid of the leading 0 and replacing it with 254
     */

    function sanitizePhoneNumber($phoneNumber): ?string
    {
        if (!$phoneNumber) {
            return null;
        }

        $phoneNumber = str_replace(" ", "", $phoneNumber); // remove spaces
        $phone_number = "254" . substr($phoneNumber, -9); // remove leading 0 and replace with 254
        return $phone_number;
    }

    /**
     *   Check if the URL is valid
     */

    function isValidUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return ! str_contains($url, 'mpesa')
            && ! str_contains($url, 'safaricom')
            && ! str_contains($url, 'daraja');
    }
}
