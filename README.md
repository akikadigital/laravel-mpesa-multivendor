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
php artisan vendor:publish --tag=mpesa-multivendor-config
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
Mpesa::using($credentials)->imsi();
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

- `$resultUrl` or `$callbackUrl` : Endpoint to send the results in case of success
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
        confirmationUrl: $confirmationUrl, // The URL to receive payment confirmation notifications.
        validationUrl: $validationUrl, // The URL to receive payment validation requests.
        ResponseType: $ResponseType, // nullable | Either Cancelled or Completed, default is Completed
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
        billRefNumber: $billRefNumber // Account reference for Customer paybills and null for customer buy goods
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

#### Send to Customer

```php
$response = Mpesa::using($credentials)
    ->b2c()
    ->send(
        phoneNumber: $phoneNumber,
        amount: $amount,
        resultUrl: $resultUrl,
        queueTimeoutUrl: $timeoutUrl,
        remarks: $remarks, // Comments that are sent along with the transaction.
        occasion: $occasion, // A reference for the transaction, such as an invoice number or account number. (Max 100 characters)
        commandId: $commandId, // The command ID for the transaction (default: 'BusinessPayment')
    );
```

#### B2C Topup

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

#### Send Bulk Invoices

```php
$response = Mpesa::using($credentials)
    ->billManager()
    ->bulkInvoice(
        invoices: $invoices
    );
```

Below is an array showing sample invoices:

```php
$invoices = [
    [
        "externalReference" => "#9932340",
        "billedFullName" => "John Doe",
        "billedPhoneNumber" => "0712345678",
        "billedPeriod" => "August 2021",
        "invoiceName" => "Jentrys",
        "dueDate" => "2021-10-12",
        "accountReference" => "1ASD678H",
        "amount" => "800",
        "invoiceItems" => [
            [
                "itemName" => "Food",
                "amount" => "700",
            ],
            [
                "itemName" => "Water",
                "amount" => "100",
            ],
        ],
    ],

    [
        "externalReference" => "#9932341",
        "billedFullName" => "Jane Smith",
        "billedPhoneNumber" => "0723456789",
        "billedPeriod" => "September 2021",
        "invoiceName" => "BlueWave Supplies",
        "dueDate" => "2021-11-15",
        "accountReference" => "2BSD789J",
        "amount" => "1500",
        "invoiceItems" => [
            [
                "itemName" => "Office Supplies",
                "amount" => "1200",
            ],
            [
                "itemName" => "Delivery Fee",
                "amount" => "300",
            ],
        ],
    ],

    [
        "externalReference" => "#9932342",
        "billedFullName" => "Peter Mwangi",
        "billedPhoneNumber" => "0734567890",
        "billedPeriod" => "October 2021",
        "invoiceName" => "Tech Solutions",
        "dueDate" => "2021-12-01",
        "accountReference" => "3CSD890K",
        "amount" => "2500",
        "invoiceItems" => [
            [
                "itemName" => "Laptop Repair",
                "amount" => "2000",
            ],
            [
                "itemName" => "Spare Parts",
                "amount" => "500",
            ],
        ],
    ],
];
```

#### Cancel Single Invoice

```php
$response = Mpesa::using($credentials)
    ->billManager()
    ->cancelSingleInvoice(
        externalReference: $externalReference // The unique reference of the invoice to be cancelled.
    );
```

#### Cancel Bulk Invoices

```php
$response = Mpesa::using($credentials)
    ->billManager()
    ->cancelBulkInvoices(
        externalReferences: $externalReferences // An array of unique references for the invoices to be cancelled.
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

### IMSI

Get the IMSI number for a given phone number.

_NOTE: This API is charged per request, so use it only when necessary (e.g., for fraud prevention or network analysis)._

```php
$response = Mpesa::using($credentials)
    ->imsi()
    ->query(
        phoneNumber: $phoneNumber
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
