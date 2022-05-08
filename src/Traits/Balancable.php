<?php

namespace Dinhdjj\Transaction\Traits;

trait Balancable
{
    use BalanceReceivable;
    use BalanceTransferable;

    /** Balance attribute of the model */
    public function getBalanceAttribute(): int
    {
        return $this->getReceivedBalanceAttribute() - $this->getTransferredBalanceAttribute();
    }

    /** Determine wether the model can transfer a given amount balance */
    public function canTransferBalance(int $amount): bool
    {
        return $amount >= 0 && $this->balance >= $amount;
    }
}
