<?php

use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\HasTransactionsUser;
use Dinhdjj\Transaction\Tests\SoftDeleteHasTransactionsUser;
use Illuminate\Database\Eloquent\Relations\MorphMany;

beforeEach(function (): void {
    $this->user = HasTransactionsUser::create();
    $this->transactions = Transaction::factory(2)->create([
        'transferer_id' => $this->user->id,
        'transferer_type' => $this->user->getMorphClass(),
    ]);
});

it('has transferred transactions', function (): void {
    expect($this->user->transferredTransactions())->toBeInstanceOf(MorphMany::class);
    expect($this->user->transferredTransactions)->toHaveCount(2);
    expect($this->user->transferredTransactions[0])->toBeInstanceOf(Transaction::class);
});

test('model auto detach transactions on delete', function (): void {
    $this->user->delete();

    expect($this->transactions[0]->refresh()->transferer_id)->toBeNull();
    expect($this->transactions[0]->refresh()->transferer_type)->toBeNull();
    expect($this->transactions[1]->refresh()->transferer_id)->toBeNull();
    expect($this->transactions[1]->refresh()->transferer_type)->toBeNull();
});

test('model auto delete transactions on delete', function (): void {
    $this->transactions->each(fn ($transaction) => $transaction->update([
        'receiver_id' => null,
        'receiver_type' => null,
    ]));
    $this->user->delete();

    expect($this->transactions[0]->exists())->toBeFalse();
    expect($this->transactions[1]->exists())->toBeFalse();
});

test('model not delete transactions on soft delete', function (): void {
    $user = SoftDeleteHasTransactionsUser::create();
    $transactions = Transaction::factory(2)->create([
        'transferer_id' => $user->id,
        'transferer_type' => $user->getMorphClass(),
    ]);

    $user->delete();

    expect($transactions[0]->refresh()->transferer_id)->toEqual($user->id);
    expect($transactions[0]->refresh()->transferer_type)->toEqual($user->getMorphClass());
    expect($transactions[1]->refresh()->transferer_id)->toEqual($user->id);
    expect($transactions[1]->refresh()->transferer_type)->toEqual($user->getMorphClass());
});
