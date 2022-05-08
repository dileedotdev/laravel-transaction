<?php

namespace Dinhdjj\Transaction\Database\Factories;

use Dinhdjj\Transaction\Models\Transaction;
use Dinhdjj\Transaction\Tests\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'transferer_id' => User::create(),
            'transferer_type' => (new User())->getMorphClass(),

            'receiver_id' => User::create(),
            'receiver_type' => (new User())->getMorphClass(),

            'amount' => 10000,
            'message' => $this->faker->sentence(),
        ];
    }
}
