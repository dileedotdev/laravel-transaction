<?php

use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

uses(TestCase::class)->in(__DIR__);

function shouldCallForgetRelatedCachesMethodOnTransaction(Transaction $transaction, int $times = null): void
{
    Cache::shouldReceive('forget')
        ->times($times)
        ->with($transaction->transferer_type.'.'.$transaction->transferer_id.'.transferred_balance')
        ->andReturn(true)
    ;
    Cache::shouldReceive('forget')
        ->times($times)
        ->with($transaction->receiver_type.'.'.$transaction->receiver_id.'.received_balance')
    ;
}
