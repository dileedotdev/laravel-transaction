<?php

use Dinhdjj\Transaction\Exceptions\RejectedBalanceException;
use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\BalancableUser;

beforeEach(function (): void {
    $this->user = BalancableUser::create();
    $this->transaction = Transaction::factory()->create([
        'receiver_id' => $this->user->getKey(),
        'receiver_type' => $this->user->getMorphClass(),
    ]);
});

test('its canReceiveBalance method accept balance amount >= 0', function (): void {
    expect($this->user->canReceiveBalance(1000000))->toBe(true);
    expect($this->user->canReceiveBalance(1))->toBe(true);
    expect($this->user->canReceiveBalance(0))->toBe(true);

    expect($this->user->canReceiveBalance(-1))->toBe(false);
    expect($this->user->canReceiveBalance(-1000000))->toBe(false);
});

test('its receiveBalance throws RejectedBalanceException if balance amount < 0', function (): void {
    $this->transaction->amount = -1;
    $this->user->receiveBalance($this->transaction);
})->expectException(RejectedBalanceException::class);

test('its receiveBalance throws InvalidArgumentException if invalid transaction', function (): void {
    $this->transaction->receiver_id = 'invalid';
    $this->transaction->receiver_type = 'invalid';
    $this->user->receiveBalance($this->transaction);
})->expectException(InvalidArgumentException::class);

test('its receiveBalance work', function (): void {
    $this->user->receiveBalance($this->transaction);

    expect($this->user->onReceiveBalanceTimes)->toHaveCount(1);
    expect($this->user->onReceiveBalanceTimes[0])->toBe($this->transaction);
});

test('its receiveBalanceFromAnonymous throws RejectedBalanceException if balance amount < 0', function (): void {
    $this->user->receiveBalanceFromAnonymous(-1, 'message');
})->expectException(RejectedBalanceException::class);

test('its receiveBalanceFromAnonymous work', function (): void {
    $transaction = $this->user->receiveBalanceFromAnonymous(1000, 'message');

    expect($transaction->receiver_id)->toBe($this->user->getKey());
    expect($transaction->receiver_type)->toBe($this->user->getMorphClass());
    expect($transaction->transferer_id)->toBeNull();
    expect($transaction->transferer_type)->toBeNull();
    expect($transaction->amount)->toBe(1000);
    expect($transaction->message)->toBe('message');
});

it('has received_balance attribute', function (): void {
    expect($this->user->getReceivedBalanceAttribute())->toBe($this->transaction->amount);
});

it('prevent caching disaster on forceReceiveBalanceFromAnonymous', function (): void {
    $callback = function (Transaction $transaction): void {
        shouldCallForgetRelatedCachesMethodOnTransaction($transaction, 1);

        throw new Exception('test');
    };

    $this->user->onReceiveBalanceCallbacks[] = $callback;
    $this->user->forceReceiveBalanceFromAnonymous(1000, 'message');
})->throws(Exception::class, 'test');
