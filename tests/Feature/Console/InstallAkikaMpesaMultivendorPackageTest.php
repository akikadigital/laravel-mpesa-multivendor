<?php

use Illuminate\Support\Facades\File;

uses()->group('console', 'install-command');

function packageConfigPath(string $path = ''): string
{
    $basePath = sys_get_temp_dir() . '/mpesa-package-config';

    if (! is_dir($basePath)) {
        mkdir($basePath, 0777, true);
    }

    return $basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
}

beforeEach(function () {
    app()->useConfigPath(packageConfigPath());

    File::delete(packageConfigPath('mpesa.php'));
});

afterEach(function () {
    File::delete(packageConfigPath('mpesa.php'));
});

it('publishes config when config file does not exist', function () {
    $this->artisan('mpesa-multivendor:install')
        ->expectsOutput('Checking if Akika/LaravelMpesaMultivendor package config file exists...')
        ->expectsOutput('Akika/LaravelMpesaMultivendor package config file does not exist.')
        ->expectsOutput('Publishing Akika/LaravelMpesaMultivendor package config file...')
        ->expectsOutput('Akika/LaravelMpesaMultivendor package config file published successfully.')
        ->assertSuccessful();
});

it('does not overwrite config when user declines confirmation', function () {
    File::put(packageConfigPath('mpesa.php'), '<?php return [];');

    $this->artisan('mpesa-multivendor:install')
        ->expectsOutput('Checking if Akika/LaravelMpesaMultivendor package config file exists...')
        ->expectsOutput('Akika/LaravelMpesaMultivendor package config file already exists.')
        ->expectsConfirmation('Do you want to overwrite existing config file?', 'no')
        ->expectsOutput('Publishing Akika/LaravelMpesaMultivendor package config file cancelled.')
        ->assertSuccessful();
});

it('overwrites config when user accepts confirmation', function () {
    File::put(packageConfigPath('mpesa.php'), '<?php return [];');

    $this->artisan('mpesa-multivendor:install')
        ->expectsOutput('Checking if Akika/LaravelMpesaMultivendor package config file exists...')
        ->expectsOutput('Akika/LaravelMpesaMultivendor package config file already exists.')
        ->expectsConfirmation('Do you want to overwrite existing config file?', 'yes')
        ->expectsOutput('Publishing Akika/LaravelMpesaMultivendor package config file...')
        ->expectsOutput('Akika/LaravelMpesaMultivendor package config file published successfully.')
        ->assertSuccessful();
});
