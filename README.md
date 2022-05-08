# Give laravel model ability transfer/receive balance

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dinhdjj/laravel-transaction.svg?style=flat-square)](https://packagist.org/packages/dinhdjj/laravel-transaction)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/dinhdjj/laravel-transaction/run-tests?label=tests)](https://github.com/dinhdjj/laravel-transaction/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/dinhdjj/laravel-transaction/Check%20&%20fix%20styling?label=code%20style)](https://github.com/dinhdjj/laravel-transaction/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dinhdjj/laravel-transaction.svg?style=flat-square)](https://packagist.org/packages/dinhdjj/laravel-transaction)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require dinhdjj/laravel-transaction
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="transaction-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="transaction-config"
```

This is the contents of the published config file:

```php
return [
    'table' => env('TRANSACTION_TABLE', 'transactions'),
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="transaction-views"
```

## Usage

In your model you want give it ability transfer/receive balance
Just uses `Balancable` trait implements `Balancable` interface

```php
namespace App\Models\User;

use Dinhdjj\Transaction\Interfaces\Balancable as InterfacesBalancable;
use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Traits\Balancable;

class User extends Model implements InterfaceBalancable
{
    use Balancable;

    protected function onReceiveBalance(Transaction $transaction): void
    {
    }

    protected function onTransferBalance(Transaction $transaction): void
    {
    }
}
```

To transfer/receiver

```php
    $user1 = User::find(1);
    $user2 = User::find(2);

    // If you don't check don't worry it will throws exception
    if($user1->canTransferBalance(200), $user2->canReceiveBalance(200))
        $transaction = $user1->transferBalance($user2, 200, 'message');

    $user1->balance // get current balance
    $user1->transferredBalance 
    $user1->receivedBalance
    
    // get all transferred transactions
    // It used standard morph many relationship
    $user1->transferredTransactions 
    $user1->receivedTransactions()->get()

    // Other
    $user1->receiveBalanceFromAnonymous(...);
    // bypass the checking
    $user1->forceReceiveBalanceFromAnonymous(...);
    $user1->transferBalanceToAnonymous(...);
    $user1->forceTransferBalanceToAnonymous(...);
```

If you want use a part, `BalanceReceivable`, `BalanceTransferable`, `HasTransferredTransactions`, `HasReceivedTransactions` both traits and interfaces will help you

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [dinhdjj](https://github.com/dinhdjj)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
