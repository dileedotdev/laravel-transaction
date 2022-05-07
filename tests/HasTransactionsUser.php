<?php

namespace Dinhdjj\Transaction\Tests;

use Dinhdjj\Transaction\Traits\HasReceivedTransactions;
use Dinhdjj\Transaction\Traits\HasTransferredTransactions;

class HasTransactionsUser extends User
{
    use HasReceivedTransactions;
    use HasTransferredTransactions;
}
