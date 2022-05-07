<?php

use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\HasTransactionsUser;
use Illuminate\Database\Eloquent\Relations\MorphMany;

beforeEach(function () {
    $this->user = HasTransactionsUser::create();
    $this->transactions = Transaction::factory(2)->create([
        'transferer_id' => $this->user->id,
        'transferer_type' => $this->user->getMorphClass(),
    ]);
});

it('has transferred transactions', function () {
    expect($this->user->transferredTransactions())->toBeInstanceOf(MorphMany::class);
    expect($this->user->transferredTransactions)->toHaveCount(2);
    expect($this->user->transferredTransactions[0])->toBeInstanceOf(Transaction::class);
});

test('model auto detach transactions on delete', function () {
    $this->user->delete();

    expect($this->transactions[0]->refresh()->transferer_id)->toBeNull();
    expect($this->transactions[0]->refresh()->transferer_type)->toBeNull();
    expect($this->transactions[1]->refresh()->transferer_id)->toBeNull();
    expect($this->transactions[1]->refresh()->transferer_type)->toBeNull();
});

test('model auto delete transactions on delete', function () {
    $this->transactions->each(fn ($transaction) => $transaction->update([
        'receiver_id' => null,
        'receiver_type' => null,
    ]));
    $this->user->delete();

    expect($this->transactions[0]->exists())->toBeFalse();
    expect($this->transactions[1]->exists())->toBeFalse();
});
