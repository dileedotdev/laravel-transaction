<?php

namespace Dinhdjj\Transaction\Interfaces;

use Dinhdjj\Transaction\Models\Transaction;

interface BalanceReceivable
{
    /**
     * Determine whether can receive the given amount balance.
     */
    public function canReceiveBalance(int $amount): bool;

    /**
     * Receive the given amount balance.
     */
    public function receiveBalance(Transaction $transaction): void;
}
