<?php

use Akika\LaravelMpesaMultivendor\Support\Helpers\MpesaHelper;

uses()->group('support', 'helpers', 'mpesa-helper');

function mpesaHelper(): object
{
    return new class {
        use MpesaHelper;
    };
}

it('returns identifier type codes', function () {
    $helper = mpesaHelper();

    expect($helper->getIdentifierType('msisdn'))->toBe(1)
        ->and($helper->getIdentifierType('tillnumber'))->toBe(2)
        ->and($helper->getIdentifierType('shortcode'))->toBe(4)
        ->and($helper->getIdentifierType('paybill'))->toBe(4)
        ->and($helper->getIdentifierType('MSISDN'))->toBe(1)
        ->and($helper->getIdentifierType('PAYBILL'))->toBe(4);
});

it('throws exception for invalid identifier type', function () {
    mpesaHelper()->getIdentifierType('invalid-type');
})->throws(InvalidArgumentException::class, 'Invalid identifier type [invalid-type]');

it('returns ratiba transaction type values', function () {
    $helper = mpesaHelper();

    expect($helper->ratibaTransactionType('paybill'))
        ->toBe('Standing Order Customer Pay Bill')
        ->and($helper->ratibaTransactionType('tillnumber'))
        ->toBe('Standing Order Customer Pay Merchant')
        ->and($helper->ratibaTransactionType('PAYBILL'))
        ->toBe('Standing Order Customer Pay Bill')
        ->and($helper->ratibaTransactionType('TILLNUMBER'))
        ->toBe('Standing Order Customer Pay Merchant');
});

it('throws exception for invalid ratiba transaction type', function () {
    mpesaHelper()->ratibaTransactionType('invalid-type');
})->throws(InvalidArgumentException::class, 'Invalid Ratiba transaction type [invalid-type]');

it('returns ratiba frequency codes', function () {
    $helper = mpesaHelper();

    expect($helper->ratibaFrequency('one-off'))->toBe(1)
        ->and($helper->ratibaFrequency('daily'))->toBe(2)
        ->and($helper->ratibaFrequency('weekly'))->toBe(3)
        ->and($helper->ratibaFrequency('monthly'))->toBe(4)
        ->and($helper->ratibaFrequency('bi-monthly'))->toBe(5)
        ->and($helper->ratibaFrequency('quarterly'))->toBe(6)
        ->and($helper->ratibaFrequency('half-year'))->toBe(7)
        ->and($helper->ratibaFrequency('yearly'))->toBe(8)
        ->and($helper->ratibaFrequency('DAILY'))->toBe(2)
        ->and($helper->ratibaFrequency('MONTHLY'))->toBe(4);
});

it('throws exception for invalid ratiba frequency', function () {
    mpesaHelper()->ratibaFrequency('everyday');
})->throws(InvalidArgumentException::class, 'Invalid frequency [everyday]');

it('returns null when phone number is empty or null', function () {
    $helper = mpesaHelper();

    expect($helper->sanitizePhoneNumber(null))->toBeNull()
        ->and($helper->sanitizePhoneNumber(''))->toBeNull();
});

it('sanitizes kenyan phone numbers', function () {
    $helper = mpesaHelper();

    expect($helper->sanitizePhoneNumber('0712345678'))->toBe('254712345678')
        ->and($helper->sanitizePhoneNumber('712345678'))->toBe('254712345678')
        ->and($helper->sanitizePhoneNumber('+254712345678'))->toBe('254712345678')
        ->and($helper->sanitizePhoneNumber('254712345678'))->toBe('254712345678')
        ->and($helper->sanitizePhoneNumber('0112345678'))->toBe('254112345678')
        ->and($helper->sanitizePhoneNumber('112345678'))->toBe('254112345678')
        ->and($helper->sanitizePhoneNumber('+254 712 345 678'))->toBe('254712345678')
        ->and($helper->sanitizePhoneNumber('(0712) 345-678'))->toBe('254712345678');
});

it('throws exception for invalid kenyan phone number', function () {
    mpesaHelper()->sanitizePhoneNumber('07234567');
})->throws(InvalidArgumentException::class, 'Invalid Kenyan phone number.');

it('throws exception for non kenyan phone number', function () {
    mpesaHelper()->sanitizePhoneNumber('+255712345678');
})->throws(InvalidArgumentException::class, 'Invalid Kenyan phone number.');

it('validates acceptable callback urls', function () {
    $helper = mpesaHelper();

    expect($helper->isValidUrl('https://example.com/callback'))->toBeTrue()
        ->and($helper->isValidUrl('https://payments.example.com/callback'))->toBeTrue()
        ->and($helper->isValidUrl('http://example.com/callback'))->toBeTrue();
});

it('rejects invalid urls', function () {
    $helper = mpesaHelper();

    expect($helper->isValidUrl('invalid-url'))->toBeFalse()
        ->and($helper->isValidUrl('example.com/callback'))->toBeFalse()
        ->and($helper->isValidUrl(''))->toBeFalse();
});

it('rejects urls containing blacklisted keywords', function () {
    $helper = mpesaHelper();

    expect($helper->isValidUrl('https://example.com/mpesa/callback'))->toBeFalse()
        ->and($helper->isValidUrl('https://safaricom.example.com/callback'))->toBeFalse()
        ->and($helper->isValidUrl('https://example.com/daraja/callback'))->toBeFalse()
        ->and($helper->isValidUrl('https://example.com/MPESA/callback'))->toBeFalse();
});

it('passes url validation when url is valid', function () {
    $helper = mpesaHelper();

    $helper->validateUrl('https://example.com/callback', 'Invalid URL.');

    expect(true)->toBeTrue();
});

it('throws exception when url validation fails', function () {
    mpesaHelper()->validateUrl('https://example.com/mpesa/callback', 'Invalid CallbackURL.');
})->throws(InvalidArgumentException::class, 'Invalid CallbackURL.');
