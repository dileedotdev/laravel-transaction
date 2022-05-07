<?php

namespace Dinhdjj\Transaction\Traits;

use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Dinhdjj\Transaction\Models\Transaction[] $transferredTransactions
 */
trait HasTransferredTransactions
{
    protected static function bootHasTransferredTransactions()
    {
        static::deleting(function ($model) {
            $isForceDeleting = method_exists($model, 'isForceDeleting') ? $model->isForceDeleting() : true;
            if (! $isForceDeleting) {
                return;
            }

            $model->transferredTransactions->each(function (Transaction $transaction) {
                if ($transaction->receiver_id === null) {
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
