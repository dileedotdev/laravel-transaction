<?php

use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\HasTransactionsUser;
use Illuminate\Database\Eloquent\Relations\MorphMany;

beforeEach(function (): void {
    $this->user = HasTransactionsUser::create();
    $this->transactions = Transaction::factory(2)->create([
        'receiver_id' => $this->user->id,
        'receiver_type' => $this->user->getMorphClass(),
    ]);
});

it('has received transactions', function (): void {
    expect($this->user->receivedTransactions())->toBeInstanceOf(MorphMany::class);
    expect($this->user->receivedTransactions)->toHaveCount(2);
    expect($this->user->receivedTransactions[0])->toBeInstanceOf(Transaction::class);
});

test('model auto detach transactions on delete', function (): void {
    $this->user->delete();

    expect($this->transactions[0]->refresh()->receiver_id)->toBeNull();
    expect($this->transactions[0]->refresh()->receiver_type)->toBeNull();
    expect($this->transactions[1]->refresh()->receiver_id)->toBeNull();
    expect($this->transactions[1]->refresh()->receiver_type)->toBeNull();
});

test('model auto delete transactions on delete', function (): void {
    $this->transactions->each(fn ($transaction) => $transaction->update([
        'transferer_id' => null,
        'transferer_type' => null,
    ]));
    $this->user->delete();

    expect($this->transactions[0]->exists())->toBeFalse();
    expect($this->transactions[1]->exists())->toBeFalse();
});
