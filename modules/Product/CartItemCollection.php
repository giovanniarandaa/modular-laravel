<?php

namespace Modules\Product;

use Illuminate\Support\Collection;
use Modules\Product\Models\Product;

class CartItemCollection
{
    /**
     * @param Collection<CartItem> $items
     */
    public function __construct(
        public Collection $items
    ) {}

    public static function fromCheckoutData(array $checkoutData): CartItemCollection
    {
        $carItems = collect($checkoutData)->map(function (array $productDetails) {
            return new CartItem(
                ProductDTO::fromEloquentModel(Product::find($productDetails['id'])),
                $productDetails['quantity']);
        });

        return new self($carItems);
    }

    public function totalInCents(): int
    {
        return $this->items->sum(function (CartItem $cartItem) {
            return $cartItem->product->priceInCents * $cartItem->quantity;
        });
    }

    /*
     * @return Collection<CartItem>
     */
    public function items(): Collection
    {
        return $this->items;
    }
}
