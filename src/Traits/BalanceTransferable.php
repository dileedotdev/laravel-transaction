<?php

namespace Dinhdjj\Transaction\Traits;

use Dinhdjj\Transaction\Exceptions\OverBalanceException;
use Dinhdjj\Transaction\Interfaces\BalanceReceivable;
use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @property int $received_balance
 */
trait BalanceTransferable
{
    use HasTransferredTransactions;

    /**
     * A hook will invoke when transferBalance is called.
     */
    public function onTransferBalance(Transaction $transaction): void
    {
    }

    /**
     * Determine whether can transfer the given amount balance.
     */
    abstract public function canTransferBalance(int $amount): bool;

    /**
     * Transfer the given amount balance.
     *
     * @throws \Dinhdjj\Transaction\Exceptions\OverBalanceException
     */
    public function transferBalance(Model & BalanceReceivable $receiver, int $amount, string $message = null): Transaction
    {
        if (!$this->canTransferBalance($amount)) {
            throw new OverBalanceException('The given amount is over balance of transferer.');
        }

        return $this->forceTransferBalance($receiver, $amount, $message);
    }

    /**
     * Transfer the given amount balance but bypass checking.
     */
    public function forceTransferBalance(Model & BalanceReceivable $receiver, int $amount, string $message = null): Transaction
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'transferer_id' => $this->getKey(),
                'transferer_type' => $this->getMorphClass(),
                'receiver_id' => $receiver->getKey(),
                'receiver_type' => $receiver->getMorphClass(),
                'amount' => $amount,
                'message' => $message,
            ]);
            $this->onTransferBalance($transaction);
            $receiver->receiveBalance($transaction);

            DB::commit();
        } catch (\Throwable $th) {
            // If the transaction is created, forget related caches.
            // !If the related balance attributes cache inside try/catch this will be a disaster.
            $transaction && $transaction->forgetRelatedCaches();

            DB::rollBack();
            throw $th;
        }

        return $transaction;
    }

    /** Time for caching transferred balance attribute */
    protected function cachedTransferredBalanceTime(): int
    {
        return 60 * 60 * 2; // 2 hours
    }

    /** Total balance that the model has transferred */
    public function getTransferredBalanceAttribute(): int
    {
        return Cache::remember(
            $this->getMorphClass().'.'.$this->getKey().'.transferred_balance',
            $this->cachedTransferredBalanceTime(),
            fn (): int => $this->transferredTransactions()->sum('amount')
        );
    }

    /** transfer model balance to anonymous */
    public function transferBalanceToAnonymous(int $amount, string $message): Transaction
    {
        if (!$this->canTransferBalance($amount)) {
            throw new OverBalanceException('The given amount is over balance of transferer.');
        }

        return $this->forceTransferBalanceToAnonymous($amount, $message);
    }

    /** Transfer model balance to anonymous and bypass checking */
    public function forceTransferBalanceToAnonymous(int $amount, string $message): Transaction
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'transferer_id' => $this->getKey(),
                'transferer_type' => $this->getMorphClass(),
                'amount' => $amount,
                'message' => $message,
            ]);
            $this->onTransferBalance($transaction);

            DB::commit();
        } catch (\Throwable $th) {
            // If the transaction is created, forget related caches.
            // !If the related balance attributes cache inside try/catch this will be a disaster.
            $transaction && $transaction->forgetRelatedCaches();

            DB::rollBack();
            throw $th;
        }

        return $transaction;
    }
}
