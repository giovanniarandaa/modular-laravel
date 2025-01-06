<?php

namespace Modules\Product;

readonly class CartItem
{
    public function __construct(
        public ProductDTO $product,
        public int $quantity
    ) {}
}
