<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public const LIST_CACHE_KEY = 'products.list';

    public static function productCacheKey(int $id): string
    {
        return "product.{$id}";
    }

    public function created(Product $product): void
    {
        $this->invalidateCache();
    }

    public function updated(Product $product): void
    {
        $this->invalidateCache();
    }

    public function deleted(Product $product): void
    {
        $this->invalidateCache();
    }

    private function invalidateCache(): void
    {
        Cache::forget(self::LIST_CACHE_KEY);
    }
}
