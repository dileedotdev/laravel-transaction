<?php

namespace Dinhdjj\Transaction\Interfaces;

use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

interface BalanceTransferable
{
    /**
     * Determine whether can transfer the given amount balance.
     */
    public function canTransferBalance(int $amount): bool;

    /**
     * Transfer the given amount balance.
     */
    public function transferBalance(BalanceReceivable & Model $receiver, int $amount, string $message = null): Transaction;
}
