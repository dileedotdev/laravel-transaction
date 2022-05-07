<?php

namespace Dinhdjj\Transaction\Tests;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeleteHasTransactionsUser extends HasTransactionsUser
{
    use SoftDeletes;
}
