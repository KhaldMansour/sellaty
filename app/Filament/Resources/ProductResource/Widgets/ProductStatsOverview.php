<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class ProductStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();

        $currentMonthStart = $now->copy()->startOfMonth();
        $currentMonthEnd = $now->copy()->endOfMonth();

        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $currentMonthProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $prevMonthProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->count();

        $percent = $prevMonthProducts
            ? round((($currentMonthProducts - $prevMonthProducts) / $prevMonthProducts) * 100, 1)
            : null;

        $percent = $prevMonthProducts ? round((($currentMonthProducts - $prevMonthProducts) / $prevMonthProducts) * 100, 1) : 0;

        $startWeek = now()->startOfWeek()->format('M d');
        $endWeek = now()->format('M d');

        return [
            Stat::make('New Active Products This Month', number_format($currentMonthProducts))
                ->description(($percent >= 0 ? '+' : '') . $percent . '%')
                ->descriptionIcon($percent >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->descriptionColor($percent >= 0 ? 'success' : 'danger')
                ,

            Stat::make('New Today', Product::whereDate('created_at', today())->count()),
            Stat::make("New This Week ({$startWeek} - {$endWeek})", Product::whereBetween('created_at', [now()->copy()->startOfWeek(), now()])->where('status', Product::STATUS_ACTIVE)->count()),
            Stat::make('Avg Products/User', round(User::withCount('products')->get()->avg('products_count'), 2)),
            Stat::make('Max Products/User', User::withCount('products')->get()->max('products_count')),
        ];
    }
}
