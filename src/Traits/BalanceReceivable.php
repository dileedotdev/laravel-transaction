<?php

namespace Dinhdjj\Transaction\Traits;

use Dinhdjj\Transaction\Exceptions\RejectedBalanceException;
use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait BalanceReceivable
{
    use HasReceivedTransactions;

    /**
     * Determine whether can receive the given amount balance.
     */
    public function canReceiveBalance(int $amount): bool
    {
        return $amount >= 0;
    }

    /**
     * A hook will invoke when receiveBalance is called.
     */
    protected function onReceiveBalance(Transaction $transaction): void
    {
    }

    /**
     * Receive the given amount balance.
     *
     * @throws \Dinhdjj\Transaction\Exceptions\RejectedBalanceException
     * @throws \InvalidArgumentException                                if $transaction is not for the model
     */
    public function receiveBalance(Transaction $transaction): void
    {
        if (!$this->canReceiveBalance($transaction->amount)) {
            throw new RejectedBalanceException('The model rejected receive balance.');
        }

        if ($transaction->receiver_id !== $this->getKey() || $transaction->receiver_type !== $this->getMorphClass()) {
            throw new InvalidArgumentException('The given transaction is not for this model.');
        }

        $this->onReceiveBalance($transaction);
    }

    /** Time for caching received balance attribute */
    protected function cachedReceivedBalanceAttributeTime(): int
    {
        return 60 * 60 * 2; // 2 hours
    }

    /** Total balance that the model has received */
    public function getReceivedBalanceAttribute(): int
    {
        return Cache::remember(
            $this->getMorphClass().'.'.$this->getKey().'.received_balance',
            $this->cachedReceivedBalanceAttributeTime(),
            fn (): int => $this->receivedTransactions()->sum('amount'),
        );
    }

    /**
     * Make the model receive balance from anonymous.
     *
     * @throws \Dinhdjj\Transaction\Exceptions\RejectedBalanceException
     */
    public function receiveBalanceFromAnonymous(int $amount, string $message): Transaction
    {
        if (!$this->canReceiveBalance($amount)) {
            throw new RejectedBalanceException('The model rejected receive balance.');
        }

        return $this->forceReceiveBalanceFromAnonymous($amount, $message);
    }

    /**
     * Make the model receive balance from anonymous and bypass checking.
     */
    public function forceReceiveBalanceFromAnonymous(int $amount, string $message): Transaction
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'receiver_id' => $this->getKey(),
                'receiver_type' => $this->getMorphClass(),
                'amount' => $amount,
                'message' => $message,
            ]);
            $this->onReceiveBalance($transaction);

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
