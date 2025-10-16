<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function created(Product $product): void
    {
        if ($product->status === Product::STATUS_ACTIVE) {
            if (!Cache::has('products_cache_version')) {
                Cache::forever('products_cache_version', 1);
            } else {
                Cache::increment('products_cache_version');
            }
        }
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged(['status', 'price', 'updated_at'])) {
            if (!Cache::has('products_cache_version')) {
                Cache::forever('products_cache_version', 1);
            } else {
                Cache::increment('products_cache_version');
            }
        }
    }

    public function deleted(Product $product): void
    {
        if ($product->status === Product::STATUS_ACTIVE) {
            if (!Cache::has('products_cache_version')) {
                Cache::forever('products_cache_version', 1);
            } else {
                Cache::increment('products_cache_version');
            }
        }
    }
}
