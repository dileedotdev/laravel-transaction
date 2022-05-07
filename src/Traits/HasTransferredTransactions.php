<?php

namespace Dinhdjj\Transaction\Traits;

use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\Dinhdjj\Transaction\Models\Transaction[] $transferredTransactions
 */
trait HasTransferredTransactions
{
    protected static function bootHasTransferredTransactions(): void
    {
        static::deleting(function ($model): void {
            $isForceDeleting = method_exists($model, 'isForceDeleting') ? $model->isForceDeleting() : true;
            if (!$isForceDeleting) {
                return;
            }

            $model->transferredTransactions->each(function (Transaction $transaction): void {
                if (null === $transaction->receiver_id) {
                    $transaction->delete();
                }

                $transaction->update([
                    'transferer_id' => null,
                    'transferer_type' => null,
                ]);
            });
        });
    }

    /** Transferred transaction relationships */
    public function transferredTransactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transferer');
    }
}
