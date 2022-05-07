<?php

namespace Dinhdjj\Transaction\Traits;

use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\Dinhdjj\Transaction\Models\Transaction[] $receivedTransactions
 */
trait HasReceivedTransactions
{
    protected static function bootHasReceivedTransactions(): void
    {
        static::deleting(function ($model): void {
            $isForceDeleting = method_exists($model, 'isForceDeleting') ? $model->isForceDeleting() : true;
            if (!$isForceDeleting) {
                return;
            }

            $model->receivedTransactions->each(function (Transaction $transaction): void {
                if (null === $transaction->transferer_id) {
                    $transaction->delete();
                }

                $transaction->update([
                    'receiver_id' => null,
                    'receiver_type' => null,
                ]);
            });
        });
    }

    /** received transaction relationships */
    public function receivedTransactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'receiver');
    }
}
