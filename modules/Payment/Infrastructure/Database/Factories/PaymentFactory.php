<?php

namespace Modules\Payment\Infrastructure\Database\Factories;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payment\Models\Payment;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;
    public function definition(): array
    {
        return [
            'total_in_cents' => random_int(100, 10000),
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => (string) Str::uuid(),
            'user_id' => UserFactory::new()
        ];
    }
}
