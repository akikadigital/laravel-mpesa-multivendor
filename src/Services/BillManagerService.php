<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class BillManagerService
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Opt-in a shortcode for bill manager services.
     *
     * @param string $email The email address associated with the shortcode.
     * @param string $officialContact The official contact phone number.
     * @param string $sendReminders Whether to send payment reminders (default: '1').
     * @param string $logo Optional logo URL for the bill manager account.
     * @param string $callbackUrl Optional callback URL for bill manager events.
     *
     * @return array The response from the API.
     */
    public function optIn(
        string $email,
        string $officialContact,
        string $sendReminders = '1',
        string $logo = '',
        string $callbackUrl = ''
    ): array {
        if (! $this->client->isValidUrl($callbackUrl)) {
            throw new \InvalidArgumentException('Invalid CallbackURL.');
        }

        $url = $this->client->baseUrl() . '/v1/billmanager-invoice/optin';

        $data = [
            'shortcode' => $this->client->shortcode(),
            'email' => $email,
            'officialContact' => $this->client->sanitizePhoneNumber($officialContact),
            'sendReminders' => $sendReminders,
            'logo' => $logo,
            'callbackurl' => $callbackUrl,
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Create a single invoice for a customer.
     *
     * @param string $externalReference A unique reference for the invoice.
     * @param string $billedFullName The full name of the billed customer.
     * @param string $billedPhoneNumber The phone number of the billed customer.
     * @param string $invoiceName A descriptive name for the invoice (e.g., "Water Bill").
     * @param float $amount The amount to be billed.
     * @param string $dueDate The due date for the invoice (in 'Y-m-d' format).
     * @param string $accountReference A reference for the account being billed.
     * @param string $billingPeriod The billing period (e.g., "January 2024").
     * @param array $items Optional additional billable items to include in the invoice.
     *     $items[
     *        'itemName' => 'Food',
     *        'amount' => 100, // Optional
     *      ]
     * @return array The response from the API.
     */
    public function singleInvoice(
        string $externalReference,
        string $billedFullName,
        string $billedPhoneNumber,
        string $invoiceName,
        float $amount,
        string $dueDate,
        string $accountReference,
        string $billingPeriod,
        array $items = [],
    ): array {

        $url = $this->client->baseUrl() . '/v1/billmanager-invoice/single-invoicing';

        $data = [
            'externalReference' => $externalReference,
            'billedFullName'    => $billedFullName,
            'billedPhoneNumber' => $this->client->sanitizePhoneNumber($billedPhoneNumber),
            'billedPeriod'      => $billingPeriod,
            'invoiceName'       => $invoiceName, // A descriptive invoice name for what your customer is being billed. e.g. water bill
            'dueDate'           => date('Y-m-d', strtotime($dueDate)), // This is the date you expect the customer to have paid the invoice amount.
            'accountReference'   => $accountReference, // This is a reference for the account being billed. It can be the same as the external reference or a different value that helps you identify the account.
            'amount'            => (int) ceil($amount),
            'invoiceItems'      => $items // These are additional billable items that you need included in your invoice. 
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Create multiple invoices for different customers in a single request.
     *
     * @param array $invoices An array of invoices to be created. Each invoice should have the same structure as the singleInvoice method parameters.
     * @return array The response from the API.
     */
    public function bulkInvoice(array $invoices): array
    {
        $url = $this->client->baseUrl() . '/v1/billmanager-invoice/bulk-invoicing';

        return $this->client->makeRequest($url, $invoices);
    }

    /**
     * Cancel a single invoice using its external reference.
     *
     * @param string $externalReference The unique reference of the invoice to be cancelled.
     * @return array The response from the API.
     */
    public function cancelSingleInvoice(
        string $externalReference,
    ): array {
        $url = $this->client->baseUrl() . '/v1/billmanager-invoice/cancel-single-invoice';

        $data = [
            'externalReference' => $externalReference,
        ];

        return $this->client->makeRequest($url, $data);
    }

    /**
     * Cancel multiple invoices using their external references.
     *
     * @param array $externalReferences An array of unique references for the invoices to be cancelled.
     * @return array The response from the API.
     */
    public function cancelBulkInvoices(
        array $externalReferences,
    ): array {
        $url = $this->client->baseUrl() . '/v1/billmanager-invoice/cancel-bulk-invoice';

        return $this->client->makeRequest($url, $externalReferences);
    }

    /**
     * Reconcile a payment for a bill manager invoice.
     *
     * @param string $transactionId The M-PESA generated reference.
     * @param float $amount Amount Paid In KES.
     * @param string $msisdn The customers PhoneNumber debited.
     * @param string $dateCreated The date the payment was done and recorded in the BillManager System.
     * @param string $accountReference This is the account number being invoiced that uniquely identifies a customer. It could be a customer name, business name, a property unit, a student’s name etc.
     *
     * @return array The response from the API.
     */
    public function reconciliation(
        string $transactionId,
        float $amount,
        string $msisdn,
        string $dateCreated,
        string $accountReference,
    ): array {
        $url = $this->client->baseUrl() . '/v1/billmanager-invoice/reconciliation';

        $data = [
            'transactionId' => $transactionId,
            'paidAmount' => $amount,
            'msisdn' => $this->client->sanitizePhoneNumber($msisdn),
            'dateCreated' => $dateCreated,
            'accountReference' => $accountReference,
            'shortCode' => $this->client->shortcode(),
        ];

        return $this->client->makeRequest($url, $data);
    }
}
