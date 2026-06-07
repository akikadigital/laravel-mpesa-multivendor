<?php

namespace Akika\LaravelMpesaMultivendor\Support;

use Akika\LaravelMpesaMultivendor\Support\Concerns\HandlesMpesaHelpers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
class MpesaClient
{
    use HandlesMpesaHelpers;

    protected string $environment;

    protected bool $debugMode;

    protected string $baseUrl;

    protected ?string $securityCredential = null;

    /**
     * MpesaClient constructor.
     *
     * @param MpesaCredentials $credentials
     */
    public function __construct(protected MpesaCredentials $credentials) {
        $this->environment = config('mpesa.env', 'sandbox');
        $this->debugMode = (bool) config('mpesa.debug', false);
        $this->baseUrl = config("mpesa.{$this->environment}.url", '');
    }

    /**
     * Get the Mpesa credentials.
     *
     * @return MpesaCredentials
     */
    public function credentials(): MpesaCredentials
    {
        return $this->credentials;
    }

    /**
     * Get the base URL for the current environment.
     *
     * @return string
     */
    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check if debug mode is enabled.
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    public function shortcode(): string
    {
        return $this->credentials->shortcode;
    }

    public function apiUsername(): string
    {
        return $this->credentials->apiUsername;
    }

    public function passkey(): ?string
    {
        return $this->credentials->passkey;
    }

    /**
     * Make an HTTP POST request to the M-Pesa API.
     *
     * @param string $url
     * @param array $body
     * @return array
     */
    public function makeRequest(string $url, array $body): array
    {
        $this->ensureConsumerCredentials();

        $response = Http::withToken($this->getAccessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->retry(2, 500)
            ->post($url, $body);

        if (! $response->successful()) {
            logger()->error('Daraja HTTP request failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Daraja request failed with status ' . $response->status());
        }

        return $response->json() ?? [];
    }

    /**
     * Get the M-Pesa access token, caching it for 55 minutes.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        $this->ensureConsumerCredentials();

        $cacheKey = sprintf(
            'mpesa_token:%s:%s',
            $this->credentials->shortcode ?: 'default',
            md5($this->credentials->consumerKey)
        );

        return cache()->remember(
            $cacheKey,
            now()->addMinutes(55),
            fn() => $this->fetchAccessToken()
        );
    }

    /**
     * Fetch a new M-Pesa access token from the API.
     *
     * @return string
     */
    protected function fetchAccessToken(): string
    {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';

        $response = Http::withBasicAuth(
            $this->credentials->consumerKey,
            $this->credentials->consumerSecret
        )->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Failed to fetch M-Pesa access token: ' . $response->body()
            );
        }

        $token = $response->json('access_token');

        if (! $token) {
            throw new \RuntimeException('M-Pesa access token was not returned.');
        }

        return $token;
    }

    /**
     * Generate the security credential by encrypting the API password with the M-Pesa public key.
     *
     * @return string
     */
    public function generatePassword(string $timestamp): string
    {
        if (! $this->credentials->passkey) {
            throw new \InvalidArgumentException('M-Pesa passkey is required.');
        }

        return base64_encode(
            $this->credentials->shortcode .
                $this->credentials->passkey .
                $timestamp
        );
    }

    /**
     * Get the security credential.
     *
     * @return string
     */
    public function getSecurityCredential(): string
    {
        return $this->securityCredential ??= $this->generateCertificate();
    }

    /**
     * Generate the certificate by encrypting the API password with the M-Pesa public key.
     *
     * @return string
     */
    protected function generateCertificate(): string
    {
        if (! $this->credentials->apiPassword) {
            throw new \InvalidArgumentException('M-Pesa API password is required.');
        }

        $certificate = $this->environment === 'sandbox'
            ? __DIR__ . '/../../certificates/SandboxCertificate.cer'
            : __DIR__ . '/../../certificates/ProductionCertificate.cer';

        $publicKey = File::get($certificate);

        $encrypted = null;

        if (! openssl_public_encrypt(
            $this->credentials->apiPassword,
            $encrypted,
            $publicKey,
            OPENSSL_PKCS1_PADDING
        )) {
            throw new \RuntimeException('Unable to encrypt M-Pesa initiator password.');
        }

        return base64_encode($encrypted);
    }

    /**
     * Ensure that the consumer credentials are set.
     *
     * @return void
     */
    protected function ensureConsumerCredentials(): void
    {
        if (! $this->credentials->consumerKey || ! $this->credentials->consumerSecret) {
            throw new \InvalidArgumentException('M-Pesa consumer key and secret are required.');
        }
    }
}
