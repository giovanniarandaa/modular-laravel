<?php

namespace Modules\Order\Actions;

use Illuminate\Database\DatabaseManager;
use Modules\Order\Exceptions\PaymentFailException;
use Modules\Order\Models\Order;
use Modules\Payment\Actions\CreatePaymentForOrder;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehourse\ProductStockManager;
use RuntimeException;

class PurchaseItems
{
    public function __construct(
        protected ProductStockManager $productStockManager,
        protected CreatePaymentForOrder $createPaymentForOrder,
        protected DatabaseManager $databaseManager
    )
    {

    }

    public function handle(CartItemCollection $items, PayBuddy $paymentProvider, string $paymentToken, int $userId): Order
    {
        $orderTotalInCents = $items->totalInCents();

        return $this->databaseManager->transaction(function () use ($paymentToken, $paymentProvider, $items, $userId, $orderTotalInCents) {
            $order = Order::create([
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

            $this->createPaymentForOrder->handle(
                $order->id,
                $userId,
                $orderTotalInCents,
                $paymentProvider,
                $paymentToken
            );

            return $order;
        });
    }
}
