<?php

namespace App\Actions\Product;

use App\Models\Product;

class CreateProductAction
{
    public function handle(array $data): Product
    {
        return Product::create($data);
    }
}
