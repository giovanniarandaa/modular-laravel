<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Models\Order;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;
use Modules\Product\Warehourse\ProductStockManager;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected ProductStockManager $productStockManager
    ) {

    }
    public function __invoke(CheckoutRequest $request)
    {
        $carItems = CartItemCollection::fromCheckoutData($request->input('products'));

        $orderTotalInCents = $carItems->totalInCents();

        $payBuddy = PayBuddy::make();

        try {
            $charge = $payBuddy->charge($request->input('payment_token'), $orderTotalInCents, 'Modularization');
        }catch (RuntimeException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment'
            ]);
        }

        $order = Order::create([
            'payment_id' => $charge['id'],
            'status' => 'paid',
            'payment_gateway' => 'PayBuddy',
            'total_in_cents' => $orderTotalInCents,
            'user_id' => $request->user()->id
        ]);

        foreach($carItems->items() as $carItem) {
            $this->productStockManager->decrement($carItem->product->id, $carItem->quantity);

            $order->lines()->create([
                'product_id' => $carItem->product->id,
                'product_price_in_cents' => $carItem->product->priceInCents,
                'quantity' => $carItem->quantity
            ]);
        }

        return response()->json([], 201);
    }
}
