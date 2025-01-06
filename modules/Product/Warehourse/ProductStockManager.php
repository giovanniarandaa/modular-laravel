<?php

namespace Modules\Product\Warehourse;

use Modules\Product\Models\Product;

class ProductStockManager
{
    public function decrement(int $productId, int $amount): void {
        Product::query()->find($productId)?->decrement('stock', $amount);
    }
}
