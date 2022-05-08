<?php

namespace Dinhdjj\Transaction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int|null                                      $transferer_id
 * @property string|null                                   $transferer_type
 * @property int|null                                      $receiver_id
 * @property string|null                                   $receiver_type
 * @property int                                           $amount
 * @property string|null                                   $message
 * @property \Illuminate\Database\Eloquent\Model|\Eloquent $transferer
 * @property \Illuminate\Database\Eloquent\Model|\Eloquent $receiver
 */
class Transaction extends Model
{
    use HasFactory;

    protected $hidden = [
    ];
    protected $fillable = [
        'transferer_id',
        'transferer_type',
        'receiver_id',
        'receiver_type',
        'amount',
        'message',
    ];
    protected $casts = [
    ];

    /** Boot the model */
    protected static function booted(): void
    {
        static::created(function (self $transaction): void {
            $transaction->forgetRelatedCaches();
        });
        static::updated(function (self $transaction): void {
            if ($transaction->isDirty('amount')) {
                $transaction->forgetRelatedCaches();
            }
        });
        static::deleted(function (self $transaction): void {
            $transaction->forgetRelatedCaches();
        });
    }

    /** Define used table */
    public function getTable(): string
    {
        return config('transaction.table', parent::getTable());
    }

    /** The transfer model relationship */
    public function transferer()
    {
        return $this->morphTo();
    }

    /** The receiver model relationship */
    public function receiver()
    {
        return $this->morphTo();
    }

    /** Forget the related caches */
    public function forgetRelatedCaches(): void
    {
        Cache::forget($this->transferer_type.'.'.$this->transferer_id.'.transferred_balance');
        Cache::forget($this->receiver_type.'.'.$this->receiver_id.'.received_balance');
    }
}
