# Laravel Mpesa Multivendor Package by [Akika Digital](https://akika.digital)

This Laravel package provides convenient methods for integrating [Mpesa Daraja API's](https://developer.safaricom.co.ke/APIs) functionalities into your Laravel application. The package will allow using more than one shortcodes. It also includes the recent Tax Remmitance and Bill Manager APIs.

## Installation

You can install the package via composer:

```bash
composer require akika/laravel-mpesa-multivendor
```

After installing the package, enable the package using the following command:

```bash
php artisan mpesa-multivendor:install
```

The above command will not only enable the package, but also publish the config file. You can use the below command to republish the config file.

```bash
php artisan vendor:publish --tag=mpesa-config
```

This will generate a mpesa.php file in your config directory where you can set your Mpesa credentials and other configuration options.

## Architecture

The package uses a service-based architecture.

Available services:

```php
Mpesa::using($credentials)->stk();
Mpesa::using($credentials)->c2b();
Mpesa::using($credentials)->b2c();
Mpesa::using($credentials)->b2b();
Mpesa::using($credentials)->accountBalance();
Mpesa::using($credentials)->dynamicQr();
Mpesa::using($credentials)->billManager();
Mpesa::using($credentials)->taxRemittance();
Mpesa::using($credentials)->ratiba();
Mpesa::using($credentials)->transactionHistory();
Mpesa::using($credentials)->transactionStatus();
Mpesa::using($credentials)->pochi();
Mpesa::using($credentials)->org();
Mpesa::using($credentials)->reversal();
```

Credentials consists of the following array. The details are shortcode based to support multivendor.

```php
$credentials = [
    'shortcode' => '600000',
    'consumer_key' => 'xxx',
    'consumer_secret' => 'xxx',
    'api_username' => 'testapi',
    'api_password' => 'xxx',
    'passkey' => 'xxx',
];
```

## Details of the config file

```php
return [
    // Define mpesa environment
    'env' => env('MPESA_ENV', 'sandbox'),
    'debug' => env('MPESA_DEBUG_MODE', true),
    'sandbox' => [
        'url' => 'https://sandbox.safaricom.co.ke',
    ],
    'production' => [
        'url' => 'https://api.safaricom.co.ke',
    ],
    /* Optional parameters for Daraja API:
    * Ideally, these should be set from the database to allow multivendor support,
    * however, we can use this as a fallback in case the database values are not set.
    */
    'shortcode' => env('MPESA_SHORTCODE', ''),
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    'api_username' => env('MPESA_API_USERNAME', ''),
    'api_password' => env('MPESA_API_PASSWORD', ''),
    'passkey' => env('MPESA_PASSKEY', ''),
];
```

## .env file Setup

Add the following configurations into the .env file

```bash
MPESA_ENV=
MPESA_DEBUG_MODE=
```

The value is either `production` or `sandbox`

NOTE: The mpesa.php config file sets the default `MPESA_ENV` value to `sandbox`. This will always load sandbox urls.

## Function Responses

All responses, except the token generation response, conform to the responses documented on the daraja portal.

## Usage

### Initializing Mpesa

Mpesa can be initialized in two ways:

#### Using Default Configuration

```php
use Akika\LaravelMpesaMultivendor\Mpesa;

$mpesa = Mpesa::default();
```

#### Using Vendor Credentials

```php
use Akika\Mpesa\Facades\Mpesa;

$mpesa = Mpesa::using($credentials);
```

### Important Urls

Daraja utilizes the two main urls for callbacks. Timeout Url and Result Url. The two urls will also be used in this package as follows:

- `$resultUrl`/`$callbackUrl` : Endpoint to send the results in case of success
- `$timeoutUrl` : Endpoint to send the results in case of operations timeout

### Access Token Management

Access tokens are automatically generated and cached by the package.

No manual token management is required.

### Getting Account Balance

You can fetch mpesa account balance as follows:

```php
$response = Mpesa::using($credentials)
    ->accountBalance()
    ->check(
        resultUrl: $resultUrl,
        queueTimeoutUrl: $timeoutUrl
    );
```

### C2B Transactions

#### Registering URLs for C2B Transactions

You can register validation and confirmation URLs for C2B transactions:

```php
$response = Mpesa::using($credentials)
    ->c2b()
    ->registerUrls(
        confirmationUrl: $confirmationUrl,
        validationUrl: $validationUrl
    );
```

#### Simulating C2B Transactions

You can simulate payment requests from clients:

```php
$response = Mpesa::using($credentials)
    ->c2b()
    ->simulate(
        phoneNumber: $phoneNumber,
        amount: $amount,
        billRefNumber: $billRefNumber
    );
```

#### Initiating STK Push

You can initiate online payment on behalf of a customer:

```php
$response = Mpesa::using($credentials)
    ->stk()
    ->push(
        phoneNumber: $phoneNumber,
        amount: $amount,
        callbackUrl: $callbackUrl,
        accountReference: $accountReference
    );
```

#### Querying STK Push Status

You can query the result of a STK Push transaction:

```php
$response = Mpesa::using($credentials)
    ->stk()
    ->query($checkoutRequestId);
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
$response = Mpesa::using($credentials)
    ->b2c()
    ->send(
        phoneNumber: $phoneNumber,
        amount: $amount,
        resultUrl: $resultUrl,
        queueTimeoutUrl: $timeoutUrl
    );
```

### B2C Topup

This API enables you to load funds to a B2C shortcode directly for disbursement. The transaction moves money from your MMF/Working account to the recipient’s utility account.

```php
$response = Mpesa::using($credentials)
    ->b2c()
    ->topUp(
        receiverShortCode: $receiverShortCode,
        amount: $amount,
        resultUrl: $resultUrl,
        timeoutUrl: $timeoutUrl,
        remarks: $remarks,
        accountReference: $accountReference,
        requester: $requester
    );
```

- $accountReference: A unique (system generated) identifier for the transaction.
- $receiverShortCode: The shortcode to which money will be moved
- $amount: The transaction amount.
- $remarks: Any additional information to be associated with the transaction.

### Business to Business (B2B) Transactions

#### B2B Paybill/Buy Goods

You can perform Business to Business transactions:

```php
$response = Mpesa::using($credentials)
    ->b2b()
    ->send(
        toPaybill: true|false // true for paybill and false for BuyGoods
        receiverShortCode: $shortcode,
        amount: $amount,
        resultUrl: $resultUrl,
        queueTimeoutUrl: $timeoutUrl,
        accountReference: $accountReference // Optional
        senderShortCode: $senderShortCode,
        initiator: $initiator,
        requester: $requester
    );
```

#### B2B Express Checkout

```php
$response = Mpesa::using($credentials)
    ->b2b()
    ->expressCheckout(
        partnerName: $partnerName, // The name of the organization that receives the transaction
        destShortcode: $destShortcode,
        amount: $amount,
        paymentReference: $paymentReference, // The reference for the payment
        callbackUrl: $callbackUrl 
        requestRefID: $requestRefID
    );
```

### QR Code Generation

You can generate QR codes for making payments:

```php
$response = Mpesa::using($credentials)
    ->dynamicQr()
    ->generate(
        merchantName: 'Akika Digital',
        reference: 'INV001',
        amount: 100
    );
```

- `$amount` is an optional field

### Bill Manager

You can optin to the bill manager service and send invoices:

#### Opt in

```php
$response = Mpesa::using($credentials)
    ->billManager()
    ->optin(
        shortcode: $shortcode$,
        email: $email,
        officialContact: $officialContact,
        sendReminders: $sendReminders, // 1 or 0
        logo: $logo, // Optional logo URL for the bill manager account.
        callbackUrl: $callbackUrl
    );
```

#### Send Single Invoice

```php
$response = Mpesa::using($credentials)
    ->billManager()
    ->singleInvoice(
        externalReference: $externalReference, // A unique reference for the invoice.
        billedFullName: $billedFullName, // The full name of the billed customer.
        billedPhoneNumber: $billedPhoneNumber, // The phone number of the billed customer.
        invoiceName: $invoiceName, // A descriptive name for the invoice (e.g., "Water Bill").
        amount: $amount,
        dueDate: $dueDate, // The due date for the invoice (in 'Y-m-d' format).
        accountReference: $accountReference, // A reference for the account being billed.
        billingPeriod: $billingPeriod, // The billing period (e.g., "June 2026").
        items: $items, // Optional additional billable items to include in the invoice.
    );
```

### Tax Remittance

You can remit tax to the government using this package. To use this API, prior integration is required with KRA for tax declaration, payment registration number (PRN) generation, and exchange of other tax-related information.:

```php
$response = Mpesa::using($credentials)
    ->taxRemmitance()
    ->remit(
        amount: $amount,
        accountReference: $accountReference, // The payment registration number (PRN) issued by KRA.
        resultUrl: $resultUrl, 
        queueTimeoutUrl: $queueTimeoutUrl, // Optional remarks for the transaction (default: 'Tax remittance').
        remarks: $remarks,
        commandId: $commandId, // The due date for the invoice (in 'Y-m-d' format).
        partyA: $partyA,
    );
```

### Mpesa Ratiba

The Standing Order APIs enable teams to integrate with the standing order solution by initiating a request to create a standing order on the customer profile.

#### Create Standing Order

```php
$response = Mpesa::using($credentials)
    ->ratiba()
    ->createStandingOrder(
        name: $name,
        startDate: $startDate,
        endDate: $endDate, 
        transactionType: $transactionType,
        amount: $amount,
        phoneNumber: $phoneNumber,
        callbackUrl: $callbackUrl,
        accountReference: $accountReference,
        frequency: $frequency,
        transactionDesc: $transactionDesc
    );
```

#### Query Standing Order

```php
$response = Mpesa::using($credentials)
    ->ratiba()
    ->query(
        standingOrderId: $standingOrderId
    );
```

#### Cancel Standing Order

```php
$response = Mpesa::using($credentials)
    ->ratiba()
    ->cancel(
        standingOrderId: $standingOrderId, // Standing order to cancel
        callbackUrl: $callbackUrl,
    );
```

### Mpesa Transaction History

#### Register URL

The URL you register will be used to receive transaction hostories

```php
$response = Mpesa::using($credentials)
    ->transactionHistory()
    ->register(
        nominatedNumber: $nominatedNumber, // The phone number to receive the transaction history callbacks (in international format, e.g., 2547XXXXXXXX).
        callbackUrl: $callbackUrl,
    );
```

#### Query History

The following API takes in the start and and end dates and returns the transactions between that period.

```php
$response = Mpesa::using($credentials)
    ->transactionHistory()
    ->query(
        startDate: $startDate,
        endDate: $endDate,
        offset: $offset,
    );
```

#### Successful Response

```php
{
    "ResponseRefID": "8bab-42cc-bb85-5056a6c01e6915928124",
    "ResponseCode": "1000",
    "ResponseMessage": "Success",
    "Response": [
        [
            {
                "transactionId": "XXXXXXXXX",
                "trxDate": "2025-01-24T10:56:11+03:00",
                "msisdn": 2547XXXXXXXX,
                "sender": "MPESA",
                "transactiontype": "c2b-buy-goods-debit",
                "billreference": "",
                "amount": "2697.0",
                "organizationname": "VENDOR"
            }
        ]
    ]
}
```

## API Response Body

$response has the following as a json object

```php
{
    "OriginatorConversationID": "5118-111210482-1",
    "ConversationID": "AG_20230420_2010759fd5662ef6d054",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

## Succssful result body

A successful result body has the following structure

```php
{
 "Result":
 {
   "ResultType": "0",
   "ResultCode":"0",
   "ResultDesc": "The service request is processed successfully",
   "OriginatorConversationID":"626f6ddf-ab37-4650-b882-b1de92ec9aa4",
   "ConversationID":"12345677dfdf89099B3",
   "TransactionID":"QKA81LK5CY",
   "ResultParameters":
     {
       "ResultParameter":
          [{
           "Key":"DebitAccountBalance",
           "Value":"{Amount={CurrencyCode=KES, MinimumAmount=618683, BasicAmount=6186.83}}"
          },
          {
          "Key":"Amount",
           "Value":"190.00"
          },
           {
          "Key":"DebitPartyAffectedAccountBalance",
           "Value":"Working Account|KES|346568.83|6186.83|340382.00|0.00"
          },
           {
          "Key":"TransCompletedTime",
           "Value":"20221110110717"
          },
           {
          "Key":"DebitPartyCharges",
           "Value":""
          },
           {
          "Key":"ReceiverPartyPublicName",
           "Value":000000– Biller Companty
          },
          {
          "Key":"Currency",
           "Value":"KES"
          },
          {
           "Key":"InitiatorAccountCurrentBalance",
           "Value":"{Amount={CurrencyCode=KES, MinimumAmount=618683, BasicAmount=6186.83}}"
          }]
       },
     "ReferenceData":
       {
        "ReferenceItem":[
           {"Key":"BillReferenceNumber", "Value":"19008"},
           {"Key":"QueueTimeoutURL", "Value":"https://mydomain.com/b2b/businessbuygoods/queue/"}
         ]
      }
 }
}
```

## Unsuccessful reusult body

An unsuccessful result body has the following structure

```php
{
 "Result":
 {
   "ResultType":0,
   "ResultCode":2001,
   "ResultDesc":"The initiator information is invalid.",
   "OriginatorConversationID":"12337-23509183-5",
   "ConversationID":"AG_20200120_0000657265d5fa9ae5c0",
   "TransactionID":"OAK0000000",
   "ResultParameters":{
     "ResultParameter":{
        "Key":"BillReferenceNumber",
        "Value":12323333
      }
   },
   "ReferenceData":{
     "ReferenceItem":[
      {
        "Key":"BillReferenceNumber",
        "Value":12323333
      },
      {
        "Key":"QueueTimeoutURL",
        "Value":"https://internalapi.safaricom.co.ke/mpesa/abresults/v1/submit"
      }
      {
        "Key":"Occassion"
      }
     ]
    }
 }
}
```

## License

The Laravel Mpesa package is open-sourced software licensed under the MIT license. See the LICENSE file for details.
