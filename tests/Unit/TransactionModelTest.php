<?php

use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

it('has transfer & receiver', function (): void {
    $transaction = Transaction::factory()->create();

    expect($transaction->transferer())->toBeInstanceOf(MorphTo::class);
    expect($transaction->transferer)->toBeInstanceOf(Model::class);

    expect($transaction->receiver())->toBeInstanceOf(MorphTo::class);
    expect($transaction->receiver)->toBeInstanceOf(Model::class);
});
