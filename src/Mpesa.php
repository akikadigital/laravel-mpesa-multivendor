<?php

namespace Akika\LaravelMpesaMultivendor;

use Akika\LaravelMpesaMultivendor\Services\B2BService;
use Akika\LaravelMpesaMultivendor\Services\B2CService;
use Akika\LaravelMpesaMultivendor\Services\C2BService;
use Akika\LaravelMpesaMultivendor\Services\StkPushService;
use Akika\LaravelMpesaMultivendor\Services\AccountBalanceService;
use Akika\LaravelMpesaMultivendor\Services\BillManagerService;
use Akika\LaravelMpesaMultivendor\Services\BongaService;
use Akika\LaravelMpesaMultivendor\Services\DynamicQrService;
use Akika\LaravelMpesaMultivendor\Services\ImsiService;
use Akika\LaravelMpesaMultivendor\Services\OrganizationServce;
use Akika\LaravelMpesaMultivendor\Services\PochiService;
use Akika\LaravelMpesaMultivendor\Services\RatibaService;
use Akika\LaravelMpesaMultivendor\Services\ReversalService;
use Akika\LaravelMpesaMultivendor\Services\TaxRemittanceService;
use Akika\LaravelMpesaMultivendor\Services\TransactionHistoryService;
use Akika\LaravelMpesaMultivendor\Services\TransactionStatusService;
use Akika\LaravelMpesaMultivendor\Support\MpesaClient;
use Akika\LaravelMpesaMultivendor\Support\MpesaCredentials;

class Mpesa
{
    public function __construct(protected MpesaClient $client) {}

    /**
     * Create an instance of Mpesa using the provided credentials.
     *
     * @param array|MpesaCredentials $credentials
     * @return self
     */
    public static function using(array|MpesaCredentials $credentials): self
    {
        $credentials = is_array($credentials)
            ? MpesaCredentials::fromArray($credentials)
            : $credentials;

        return new self(new MpesaClient($credentials));
    }

    /**
     * Create an instance of Mpesa using the default credentials from the configuration file.
     *
     * @return self
     */
    public static function default(): self
    {
        return new self(
            new MpesaClient(MpesaCredentials::fromConfig())
        );
    }

    /**
     * Get the underlying MpesaClient instance.
     *
     * @return MpesaClient
     */
    public function client(): MpesaClient
    {
        return $this->client;
    }

    /**
     * Get an instance of the AccountBalanceService.
     *
     * @return AccountBalanceService
     */
    public function accountBalance(): AccountBalanceService
    {
        return new AccountBalanceService($this->client);
    }

    /**
     * Get an instance of the StkPushService.
     *
     * @return StkPushService
     */
    public function stk(): StkPushService
    {
        return new StkPushService($this->client);
    }

    /**
     * Get an instance of the C2BService.
     *
     * @return C2BService
     */
    public function c2b(): C2BService
    {
        return new C2BService($this->client);
    }

    /**
     * Get an instance of the B2CService.
     *
     * @return B2CService
     */
    public function b2c(): B2CService
    {
        return new B2CService($this->client);
    }

    /**
     * Get an instance of the B2BService.
     *
     * @return B2BService
     */
    public function b2b(): B2BService
    {
        return new B2BService($this->client);
    }

    /**
     * Get an instance of the ReversalService.
     *
     * @return ReversalService
     */
    public function reversal(): ReversalService
    {
        return new ReversalService($this->client);
    }

    /**
     * Get an instance of the TransactionStatusService.
     *
     * @return TransactionStatusService
     */
    public function status(): TransactionStatusService
    {
        return new TransactionStatusService($this->client);
    }

    /**
     * Get an instance of the DynamicQrService.
     *
     * @return DynamicQrService
     */
    public function dynamicQr(): DynamicQrService
    {
        return new DynamicQrService($this->client);
    }

    /**
     * Get an instance of the BillManagerService.
     * 
     * @return BillManagerService
     */
    public function billManager(): BillManagerService
    {
        return new BillManagerService($this->client);
    }

    /**
     * Get an instance of the TaxRemittanceService.
     *
     * @return TaxRemittanceService
     */
    public function taxRemittance(): TaxRemittanceService
    {
        return new TaxRemittanceService($this->client);
    }

    /**
     * Get an instance of the RatibaService.
     *
     * @return RatibaService
     */
    public function ratiba(): RatibaService
    {
        return new RatibaService($this->client);
    }

    /**
     * Get an instance of the TransactionHistoryService.
     *
     * @return TransactionHistoryService
     */
    public function transactionHistory(): TransactionHistoryService
    {
        return new TransactionHistoryService($this->client);
    }

    /**
     * Get an instance of the PochiService.
     *
     * @return PochiService
     */
    public function pochi(): PochiService
    {
        return new PochiService($this->client);
    }

    /**
     * Get an instance of the BongaService.
     *
     * @return BongaService
     */
    public function bonga(): BongaService
    {
        return new BongaService($this->client);
    }

    /**
     * Get an instance of the ImsiService.
     *
     * @return ImsiService
     */
    public function imsi(): ImsiService
    {
        return new ImsiService($this->client);
    }

    /**
     * Get an instance of the OrganizationService.
     *
     * @return OrganizationServce
     */
    public function org(): OrganizationServce
    {
        return new OrganizationServce($this->client);
    }
}
