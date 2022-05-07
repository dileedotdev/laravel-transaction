<?php

namespace Dinhdjj\Transaction\Traits;

use Dinhdjj\Transaction\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Dinhdjj\Transaction\Models\Transaction[] $receivedTransactions
 */
trait HasReceivedTransactions
{
    protected static function bootHasReceivedTransactions()
    {
        static::deleting(function ($model) {
            $isForceDeleting = method_exists($model, 'isForceDeleting') ? $model->isForceDeleting() : true;
            if (! $isForceDeleting) {
                return;
            }

            $model->receivedTransactions->each(function (Transaction $transaction) {
                if ($transaction->transferer_id === null) {
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
