<?php

use Dinhdjj\Transaction\Exceptions\OverBalanceException;
use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\BalancableUser;

beforeEach(function (): void {
    $this->user = BalancableUser::create();
    $this->receiver = BalancableUser::create();
    $this->transaction = Transaction::factory()->create([
        'receiver_id' => $this->user->getKey(),
        'receiver_type' => $this->user->getMorphClass(),
        'transferer_id' => $this->receiver->getKey(),
        'transferer_type' => $this->receiver->getMorphClass(),
    ]);
});

test('its transferBalance method throws OverBalanceException if invalid balance amount', function (): void {
    // determining whether amount balance is invalid or not
    // that is job of canTransferBalance method will be override by class
    // in this case that is override by Balancable trait
    $this->user->transferBalance($this->receiver, -1);
})->expectException(OverBalanceException::class);

test('its transferBalance method is working', function (): void {
    $transaction = $this->user->transferBalance($this->receiver, 1, 'message');

    expect($transaction->transferer_id)->toBe($this->user->getKey());
    expect($transaction->transferer_type)->toBe($this->user->getMorphClass());
    expect($transaction->receiver_id)->toBe($this->receiver->getKey());
    expect($transaction->receiver_type)->toBe($this->receiver->getMorphClass());
    expect($transaction->amount)->toBe(1);
    expect($transaction->message)->toBe('message');

    expect($this->user->onTransferBalanceTimes)->toHaveCount(1);
    expect($this->user->onTransferBalanceTimes[0])->toBe($transaction);
});

test('its transferBalanceToAnonymous method throws OverBalanceException', function (): void {
    // determining whether amount balance is invalid or not
    // that is job of canTransferBalance method will be override by class
    // in this case that is override by Balancable trait
    $this->user->transferBalanceToAnonymous(-1, 'message');
})->expectException(OverBalanceException::class);

test('its transferBalanceToAnonymous method is working', function (): void {
    $transaction = $this->user->transferBalanceToAnonymous(1, 'message');

    expect($transaction->transferer_id)->toBe($this->user->getKey());
    expect($transaction->transferer_type)->toBe($this->user->getMorphClass());
    expect($transaction->receiver_id)->toBeNull();
    expect($transaction->receiver_type)->toBeNull();
    expect($transaction->amount)->toBe(1);
    expect($transaction->message)->toBe('message');
    expect($this->user->onTransferBalanceTimes)->toHaveCount(1);
    expect($this->user->onTransferBalanceTimes[0])->toBe($transaction);
});

it('has transferred_balance attribute', function (): void {
    $transaction = $this->user->forceTransferBalanceToAnonymous(1, 'message');

    expect($this->user->getTransferredBalanceAttribute())->toBe($transaction->amount);
});

it('prevent caching disaster on forceTransferBalanceToAnonymous', function (): void {
    $callback = function (Transaction $transaction): void {
        shouldCallForgetRelatedCachesMethodOnTransaction($transaction, 1);

        throw new Exception('test');
    };

    $this->user->onTransferBalanceCallbacks[] = $callback;
    $this->user->forceTransferBalanceToAnonymous(1000, 'message');
})->throws(Exception::class, 'test');

it('prevent caching disaster on forceTransferBalance', function (): void {
    $callback = function (Transaction $transaction): void {
        shouldCallForgetRelatedCachesMethodOnTransaction($transaction, 1);

        throw new Exception('test');
    };

    $this->user->onTransferBalanceCallbacks[] = $callback;
    $this->user->forceTransferBalance($this->receiver, 1000, 'message');
})->throws(Exception::class, 'test');
