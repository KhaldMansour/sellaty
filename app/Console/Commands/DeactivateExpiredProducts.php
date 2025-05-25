<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class DeactivateExpiredProducts extends Command
{
    protected $signature = 'products:deactivate-expired';

    protected $description = 'Deactivate products whose listed_until date has passed';

    public function handle()
    {
        $this->info("Checking for expired products...");
    
        $expiredCount = 0; 
    
        Product::where('status', 'active')
            ->whereDate('listed_until', '<', now()->toDateString())
            ->chunkById(100, function ($products) use (&$expiredCount) {
                $ids = $products->pluck('id')->toArray();
                $expiredCount += count($ids);
    
                Product::whereIn('id', $ids)->update(['status' => 'inactive']);
            });
    
        $this->info("Expired products have been deactivated.");
        $this->info("Total expired products: {$expiredCount}");
    }
    
}
