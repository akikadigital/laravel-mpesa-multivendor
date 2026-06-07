<?php

namespace Akika\LaravelMpesaMultivendor\Support;

class MpesaCredentials
{
    /**
     * MpesaCredentials constructor.
     *
     * @param string $shortcode
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $apiUsername
     * @param string $apiPassword
     * @param string|null $passkey
     */
    public function __construct(
        public string $shortcode,
        public string $consumerKey,
        public string $consumerSecret,
        public string $apiUsername = '',
        public string $apiPassword = '',
        public ?string $passkey = null,
    ) {}

    /**
     * Create an instance of MpesaCredentials from an array of credentials.
     *
     * @param array $credentials
     * @return self
     */
    public static function fromArray(array $credentials): self
    {
        return new self(
            shortcode: $credentials['shortcode'] ?? '',
            consumerKey: $credentials['consumer_key'] ?? '',
            consumerSecret: $credentials['consumer_secret'] ?? '',
            apiUsername: $credentials['api_username'] ?? '',
            apiPassword: $credentials['api_password'] ?? '',
            passkey: $credentials['passkey'] ?? null,
        );
    }

    /**
     * Create an instance of MpesaCredentials from the configuration file.
     *
     * @return self
     */
    public static function fromConfig(): self
    {
        return new self(
            shortcode: config('mpesa.shortcode', ''),
            consumerKey: config('mpesa.consumer_key', ''),
            consumerSecret: config('mpesa.consumer_secret', ''),
            apiUsername: config('mpesa.api_username', ''),
            apiPassword: config('mpesa.api_password', ''),
            passkey: config('mpesa.passkey'),
        );
    }
}
