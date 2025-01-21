<?php

namespace Modules\Order\Actions;

use Modules\Order\Exceptions\PaymentFailException;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehourse\ProductStockManager;
use RuntimeException;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager
    )
    {

    }

    public function handle(CartItemCollection $items, PayBuddy $paymentProvider, string $paymentToken, int $userId): Order
    {
        $orderTotalInCents = $items->totalInCents();

        try {
            $charge = $paymentProvider->charge($paymentToken, $orderTotalInCents, 'Modularization');
        }catch (RuntimeException) {
            throw PaymentFailException::dueToInvalidToken();
        }

        $order = Order::create([
            'payment_id' => $charge['id'],
            'status' => 'completed',
            'total_in_cents' => $orderTotalInCents,
            'user_id' => $userId
        ]);

        foreach($items->items() as $carItem) {
            $this->productStockManager->decrement($carItem->product->id, $carItem->quantity);

            $order->lines()->create([
                'product_id' => $carItem->product->id,
                'product_price_in_cents' => $carItem->product->priceInCents,
                'quantity' => $carItem->quantity
            ]);
        }

        $payment = $order->payments()->create([
            'total_in_cents' => $orderTotalInCents,
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'payment_id' => $charge['id'],
            'user_id' => $userId,
        ]);

        return $order;
    }
}
