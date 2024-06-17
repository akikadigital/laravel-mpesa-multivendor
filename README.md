# Laravel Mpesa Multivendor Package by [Akika Digital](https://akika.digital)

This Laravel package provides convenient methods for integrating [Mpesa Daraja API's](https://developer.safaricom.co.ke/APIs) functionalities into your Laravel application. The package will allow using more than one shortcodes. It also includes the recent Tax Remmitance and Bill Manager APIs.

## Installation

You can install the package via composer:

```bash
composer require akika/laravel-mpesa-multivendor
```

After installing the package, publish the configuration file using the following command:

```bash
php artisan mpesa:install
```

This will generate a mpesa.php file in your config directory where you can set your Mpesa credentials and other configuration options.

## .env file Setup

Add the following configurations into the .env file

```
MPESA_ENV=
```

The value is either `production` or `sandbox`

NOTE: The mpesa.php config file sets the default `MPESA_ENV` value to `sandbox`. This will always load sandbox urls.

## Function Responses

All responses, except the token generation response, conform to the responses documented on the daraja portal.

## Usage

### Initializing Mpesa

```php
use Akika\LaravelMpesa\Mpesa;

$mpesa = new Mpesa($mpesaShortcode, $consumerKey, $consumerSecret, $apiUsername, $apiPassword);
```

- $mpesaShortcode: The shortcode to use for the current operation
- $consumerKey: Obtained from Daraja portal
- $consumerSecret: Obtained from Daraja portal
- $apiUsername: Mpesa portal API user's username
- $apiPassword: Mpesa portal API user's password

### Important Urls

Daraja utilizes the two main urls for callbacks. Timeout Url and Result Url. The two urls will also be used in this package as follows:

- $resultUrl : Endpoint to send the results in case of success
- $timeoutUrl : Endpoint to send the results in case of operations timeout

### Fetching Token

You can fetch the token required for Mpesa API calls as follows:

```php
$token = $mpesa->getToken();
```

### Getting Account Balance

You can fetch mpesa account balance as follows:

```php
$balance = $mpesa->getBalance($resultUrl, $timeoutUrl);
```

### C2B Transactions

#### Registering URLs for C2B Transactions

You can register validation and confirmation URLs for C2B transactions:

````php
$response = $mpesa->c2bRegisterUrl($confirmationUrl, $validationUrl);

#### Simulating C2B Transactions

You can simulate payment requests from clients:

```php
$response = $mpesa->c2bSimulate($amount, $phoneNumber, $billRefNumber, $commandID);
````

- $commandID is either `CustomerPayBillOnline` or `CustomerBuyGoodsOnline` and if not set, the package will assume `CustomerPayBillOnline`

#### Initiating STK Push

You can initiate online payment on behalf of a customer:

```php
$response = $mpesa->stkPush($accountNumber, $phoneNumber, $amount, $callbackUrl, $transactionDesc);
```

- `$transactionDesc` can be null

#### Querying STK Push Status

You can query the result of a STK Push transaction:

```php
$response = $mpesa->stkPushStatus($checkoutRequestID);
```

#### Reversing Transactions

You can reverse a C2B M-Pesa transaction:

```php
$response = $mpesa->reverse($transactionId, $amount, $receiverShortCode, $remarks, $resultUrl, $timeoutUrl, $ocassion);
```

- `$ocassion` is an optional field

### Business to Customer (B2C) Transactions

You can perform Business to Customer transactions:

```php
$response = $mpesa->b2cTransaction($oversationId, $commandID, $msisdn, $amount, $remarks, $resultUrl, $timeoutUrl, $ocassion);
```

- `$ocassion` is an optional field.

### Business to Business (B2B) Transactions

You can perform Business to Business transactions:

```php
$response = $mpesa->b2bPaybill($destShortcode, $amount, $remarks, $accountNumber, $resultUrl, $timeoutUrl, $requester);
```

- `$requester` is an optional field.

### QR Code Generation

You can generate QR codes for making payments:

```php
$response = $mpesa->dynamicQR($merchantName, $refNo, $trxCode, $cpi, $size, $amount = null);
```

- `$amount` is an optional field

### Bill Manager

You can optin to the bill manager service and send invoices:

```php
$response = $mpesa->billManagerOptin($email, $phoneNumber, $sendReminders, $logoUrl, $callbackUrl);
```

- `$sendReminders` is a boolean field. Allows true or false

$response = $mpesa->sendInvoice($reference, $billedTo, $phoneNumber, $billingPeriod, $invoiceName, $dueDate, $amount, $items);

### Tax Remittance

You can remit tax to the government:

```php
$response = $mpesa->taxRemittance($amount, $receiverShortCode, $accountReference, $remarks, $resultUrl, $timeoutUrl);
```

## License

The Laravel Mpesa package is open-sourced software licensed under the MIT license. See the LICENSE file for details.
