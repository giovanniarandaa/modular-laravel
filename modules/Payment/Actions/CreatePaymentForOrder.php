<?php

namespace Modules\Payment\Actions;

use Modules\Order\Exceptions\PaymentFailException;
use Modules\Payment\PayBuddy;
use Modules\Payment\Payment;
use RuntimeException;

class CreatePaymentForOrder
{

    /**
     * Creates a new payment for an order and marks the order as paid
     * @throws PaymentFailException If the payment token is invalid
     */
    public function handle(
        int $orderId,
        int $userId,
        int $totalInCents,
        PayBuddy $payBuddy,
        string $paymentToken
    ): Payment {
        try {
            $charge = $payBuddy->charge($paymentToken, $totalInCents, 'Modularization');
        }catch (RuntimeException) {
            throw PaymentFailException::dueToInvalidToken();
        }

        return Payment::query()->create([
            'total_in_cents' => $totalInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
            'order_id' => $orderId
        ]);
    }
}
