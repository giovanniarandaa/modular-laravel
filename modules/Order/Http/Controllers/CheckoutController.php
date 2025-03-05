<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Order\Actions\PurchaseItems;
use Modules\Order\Exceptions\PaymentFailException;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Payment\PayBuddy;
use Modules\Product\CartItemCollection;

class CheckoutController extends Controller
{
    public function __construct(
       protected PurchaseItems $purchaseItems
    ) {

    }
    public function __invoke(CheckoutRequest $request)
    {
        $carItems = CartItemCollection::fromCheckoutData($request->input('products'));

        try {
            $order = $this->purchaseItems->handle(
                items: $carItems,
                paymentProvider: PayBuddy::make(),
                paymentToken: $request->input('payment_token'),
                userId: $request->user()->id,
                userEmail: $request->user()->email
            );
        } catch (PaymentFailException) {
            throw ValidationException::withMessages([
                'payment_token' => 'We could not complete your payment'
            ]);
        }

        return response()->json([
            'order_url' => $order->url()
        ], 201);
    }
}
