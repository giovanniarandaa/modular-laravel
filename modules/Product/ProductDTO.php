<?php

namespace Modules\Product;

use Modules\Product\Models\Product;

readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public int $priceInCents,
        public int $unitsInStock
    ) {}

    public static function fromEloquentModel(Product $product): ProductDTO
    {
        return new ProductDTO(
            id: $product->id,
            priceInCents: $product->price_in_cents,
            unitsInStock: $product->stock
        );
    }
}
