<?php

use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\BalancableUser;

beforeEach(function (): void {
    $this->user = BalancableUser::create();
    $this->transaction = Transaction::factory()->create([
        'receiver_id' => $this->user->getKey(),
        'receiver_type' => $this->user->getMorphClass(),
        'amount' => 10,
    ]);
});

test('its canTransferBalance method work', function (): void {
    expect($this->user->canTransferBalance(10))->toBe(true);
    expect($this->user->canTransferBalance(11))->toBe(false);
    expect($this->user->canTransferBalance(0))->toBe(true);
    expect($this->user->canTransferBalance(-1))->toBe(false);
});

it('has balance attribute', function (): void {
    expect($this->user->getBalanceAttribute())->toBe($this->transaction->amount);
});
