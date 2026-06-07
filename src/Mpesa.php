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
    public function __construct(
        protected MpesaClient $client
    ) {}

    public static function using(array|MpesaCredentials $credentials): self
    {
        $credentials = is_array($credentials)
            ? MpesaCredentials::fromArray($credentials)
            : $credentials;

        return new self(new MpesaClient($credentials));
    }

    public static function default(): self
    {
        return new self(
            new MpesaClient(MpesaCredentials::fromConfig())
        );
    }

    public function client(): MpesaClient
    {
        return $this->client;
    }

    public function accountBalance(): AccountBalanceService
    {
        return new AccountBalanceService($this->client);
    }

    public function stk(): StkPushService
    {
        return new StkPushService($this->client);
    }

    public function c2b(): C2BService
    {
        return new C2BService($this->client);
    }

    public function b2c(): B2CService
    {
        return new B2CService($this->client);
    }

    public function b2b(): B2BService
    {
        return new B2BService($this->client);
    }

    public function reversal(): ReversalService
    {
        return new ReversalService($this->client);
    }

    public function transactionStatus(): TransactionStatusService
    {
        return new TransactionStatusService($this->client);
    }

    public function dynamicQr(): DynamicQrService
    {
        return new DynamicQrService($this->client);
    }

    public function billManager(): BillManagerService
    {
        return new BillManagerService($this->client);
    }

    public function taxRemittance(): TaxRemittanceService
    {
        return new TaxRemittanceService($this->client);
    }

    public function ratiba(): RatibaService
    {
        return new RatibaService($this->client);
    }

    public function transactionHistory(): TransactionHistoryService
    {
        return new TransactionHistoryService($this->client);
    }

    public function pochi(): PochiService
    {
        return new PochiService($this->client);
    }

    public function bonga(): BongaService
    {
        return new BongaService($this->client);
    }
}
