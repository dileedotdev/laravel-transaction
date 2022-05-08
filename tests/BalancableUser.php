<?php

namespace Dinhdjj\Transaction\Tests;

use Dinhdjj\Transaction\Interfaces\Balancable as InterfacesBalancable;
use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Traits\Balancable;

class BalancableUser extends User implements InterfacesBalancable
{
    use Balancable;

    public $onReceiveBalanceCallbacks = [];
    public $onReceiveBalanceTimes = [];

    protected function onReceiveBalance(Transaction $transaction): void
    {
        $this->onReceiveBalanceTimes[] = $transaction;
        foreach ($this->onReceiveBalanceCallbacks as $callback) {
            $callback($transaction);
        }
    }

    public $onTransferBalanceCallbacks = [];
    public $onTransferBalanceTimes = [];

    protected function onTransferBalance(Transaction $transaction): void
    {
        $this->onTransferBalanceTimes[] = $transaction;
        foreach ($this->onTransferBalanceCallbacks as $callback) {
            $callback($transaction);
        }
    }
}
