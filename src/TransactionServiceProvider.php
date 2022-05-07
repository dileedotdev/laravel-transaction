<?php

namespace Dinhdjj\Transaction;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TransactionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('transaction')
            ->hasConfigFile()
            // ->hasViews()
            ->hasMigration('create_transactions_table')
            // ->hasCommand(TransactionCommand::class)
        ;
    }
}
