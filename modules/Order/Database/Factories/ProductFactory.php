<?php

namespace Modules\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Product;
use Random\RandomException;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'price_in_cents' => random_int(100, 10000),
            'stock' => random_int(1, 10)
        ];
    }
}
