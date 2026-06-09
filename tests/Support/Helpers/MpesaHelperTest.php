<?php

namespace Akika\LaravelMpesaMultivendor\Support\Helpers;

use Akika\LaravelMpesaMultivendor\Tests\Fixtures\HelperTester;
use InvalidArgumentException;

uses()->group('helpers');

function helper()
{
    return new HelperTester();
}

it('returns identifier type codes', function () {
    $helper = helper();
    expect($helper->getIdentifierType('msisdn'))->toBe(1)
        ->and($helper->getIdentifierType('tillnumber'))->toBe(2)
        ->and($helper->getIdentifierType('shortcode'))->toBe(4)
        ->and($helper->getIdentifierType('paybill'))->toBe(4);
});

it('handles identifier type case insensitively', function () {
    $helper = helper();
    expect($helper->getIdentifierType('MSISDN'))->toBe(1)
        ->and($helper->getIdentifierType('PayBill'))->toBe(4);
});

it('throws exception for invalid identifier type', function () {
    $helper = helper();
    $helper->getIdentifierType('invalid');
})->throws(InvalidArgumentException::class, 'Invalid identifier type [invalid]');

it('returns ratiba transaction type', function () {
    $helper = helper();
    expect($helper->ratibaTransactionType('paybill'))
        ->toBe('Standing Order Customer Pay Bill')
        ->and($helper->ratibaTransactionType('tillnumber'))
        ->toBe('Standing Order Customer Pay Merchant');
});

it('handles ratiba transaction type case insensitively', function () {
    $helper = helper();
    expect($helper->ratibaTransactionType('PAYBILL'))
        ->toBe('Standing Order Customer Pay Bill');
});

it('throws exception for invalid ratiba transaction type', function () {
    $helper = helper();
    $helper->ratibaTransactionType('bank');
})->throws(InvalidArgumentException::class, 'Invalid Ratiba transaction type [bank]');

it('returns ratiba frequency codes', function () {
    $helper = helper();
    expect($helper->ratibaFrequency('one-off'))->toBe(1)
        ->and($helper->ratibaFrequency('daily'))->toBe(2)
        ->and($helper->ratibaFrequency('weekly'))->toBe(3)
        ->and($helper->ratibaFrequency('monthly'))->toBe(4)
        ->and($helper->ratibaFrequency('bi-monthly'))->toBe(5)
        ->and($helper->ratibaFrequency('quarterly'))->toBe(6)
        ->and($helper->ratibaFrequency('half-year'))->toBe(7)
        ->and($helper->ratibaFrequency('yearly'))->toBe(8);
});

it('handles ratiba frequency case insensitively', function () {
    $helper = helper();
    expect($helper->ratibaFrequency('DAILY'))->toBe(2);
});

it('throws exception for invalid ratiba frequency', function () {
    $helper = helper();
    $helper->ratibaFrequency('annually');
})->throws(InvalidArgumentException::class, 'Invalid frequency [annually]');

it('returns null for empty phone number', function () {
    $helper = helper();
    expect($helper->sanitizePhoneNumber(null))->toBeNull()
        ->and($helper->sanitizePhoneNumber(''))->toBeNull();
});

it('sanitizes phone number starting with zero', function () {
    $helper = helper();
    expect($helper->sanitizePhoneNumber('0712345678'))
        ->toBe('254712345678');
});

it('sanitizes phone number already starting with 254', function () {
    $helper = helper();
    expect($helper->sanitizePhoneNumber('254712345678'))
        ->toBe('254712345678');
});

it('sanitizes phone number starting with 7', function () {
    $helper = helper();
    expect($helper->sanitizePhoneNumber('712345678'))
        ->toBe('254712345678');
});

it('sanitizes phone number starting with 1', function () {
    $helper = helper();
    expect($helper->sanitizePhoneNumber('112345678'))
        ->toBe('254112345678');
});

it('removes spaces and symbols from phone number', function () {
    $helper = helper();
    expect($helper->sanitizePhoneNumber('+254 712 345 678'))
        ->toBe('254712345678');
});

it('throws exception for invalid phone number', function () {
    $helper = helper();
    $helper->sanitizePhoneNumber('12345');
})->throws(InvalidArgumentException::class, 'Invalid Kenyan phone number.');

it('validates a normal callback url', function () {
    $helper = helper();
    expect($helper->isValidUrl('https://example.com/callback'))
        ->toBeTrue();
});

it('rejects invalid url format', function () {
    $helper = helper();
    expect($helper->isValidUrl('not-a-url'))
        ->toBeFalse();
});

it('rejects urls containing mpesa', function () {
    $helper = helper();
    expect($helper->isValidUrl('https://example.com/mpesa/callback'))
        ->toBeFalse();
});

it('rejects urls containing safaricom', function () {
    $helper = helper();
    expect($helper->isValidUrl('https://safaricom.example.com/callback'))
        ->toBeFalse();
});

it('rejects urls containing daraja', function () {
    $helper = helper();
    expect($helper->isValidUrl('https://example.com/daraja/callback'))
        ->toBeFalse();
});

it('rejects blacklisted urls case insensitively', function () {
    $helper = helper();
    expect($helper->isValidUrl('https://example.com/MPESA/callback'))
        ->toBeFalse();
});
