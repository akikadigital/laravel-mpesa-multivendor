<?php

namespace Akika\LaravelMpesa\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

trait MpesaTrait
{
    // --------------------------------- Token Generation ---------------------------------

    /**
     *   Fetch the token from the database if it exists and is not expired
     *   If it does not exist or is expired, generate a new token and save it to the database
     */

    function getToken()
    {
        $url = $this->url . '/oauth/v1/generate?grant_type=client_credentials';
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get($url);

        return $response;
    }

    /**
     *   Make a request to the Mpesa API
     */

    function makeRequest($url, $body)
    {
        // Convert the above code to use Http
        $token = json_decode($this->getToken());
        $response = Http::withToken($token->access_token)
            ->acceptJson()
            ->post($url, $body);

        return $response;
    }

    /**
     *   Get the identifier type given the type
     */

    function getIdentifierType($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case "msisdn":
                $x = 1;
                break;
            case "tillnumber":
                $x = 2;
                break;
            case "shortcode":
                $x = 4;
                break;
        }
        return $x;
    }

    /**
     *   Generate the password for the STK push
     */

    function generatePassword()
    {
        $timestamp = Carbon::now()->format('YmdHis');
        $shortcode = config('mpesa.shortcode');
        $passkey = config('mpesa.stk_passkey');
        $password = base64_encode($shortcode . $passkey . $timestamp);

        return $password;
    }

    /**
     *   Generate the certificate for the API
     */

    function generateCertificate()
    {
        if (config('mpesa.env') == 'sandbox') {
            $publicKey = File::get(__DIR__ . '/../../certificates/SandboxCertificate.cer');
        } else {
            $publicKey = File::get(__DIR__ . '/../../certificates/ProductionCertificate.cer');
        }
        openssl_public_encrypt(config('mpesa.initiator_password'), $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    /**
     *   Sanitize the phone number by getting rid of the leading 0 and replacing it with 254
     */

    function sanitizePhoneNumber($phoneNumber)
    {
        $phoneNumber = str_replace(" ", "", $phoneNumber); // remove spaces
        $phone_number = "254" . substr($phoneNumber, -9); // remove leading 0 and replace with 254
        return $phone_number;
    }

    /**
     *   Check if the URL is valid
     */

    function isValidUrl($url)
    {
        // check if $url is a valid url and has not include keywords like mpesa,safaricom etc
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            if (strpos($url, 'mpesa') !== false || strpos($url, 'safaricom') !==false || strpos($url, 'daraja') !== false) {
                return false;
            }
            return true;
        }
    }
}
