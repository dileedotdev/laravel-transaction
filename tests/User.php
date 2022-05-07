<?php

namespace Dinhdjj\Transaction\Tests;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function getTable()
    {
        return 'users';
    }
}
