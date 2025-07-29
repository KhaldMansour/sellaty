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
        $prevMonth = $now->copy()->subMonths(1);

        $currentMonthProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();


        $prevMonthProducts = Product::where('status', Product::STATUS_ACTIVE)
            ->whereMonth('created_at', $prevMonth->month)
            ->whereYear('created_at', $prevMonth->year)
            ->count();

        $percent = $prevMonthProducts
            ? round((($currentMonthProducts - $prevMonthProducts) / $prevMonthProducts) * 100, 1)
            : null;

        $percent = $prevMonthProducts ? round((($currentMonthProducts - $prevMonthProducts) / $prevMonthProducts) * 100, 1) : 0;

        return [
            Stat::make('New Active Products This Month', number_format($currentMonthProducts))
                ->description(($percent >= 0 ? '+' : '') . $percent . '%')
                ->descriptionIcon($percent >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->descriptionColor($percent >= 0 ? 'success' : 'danger')
                ,

            Stat::make('New Today', Product::whereDate('created_at', today())->count()),
            Stat::make('New This Week', Product::whereBetween('created_at', [now()->startOfWeek(), now()])->count()),
            Stat::make('Avg Products/User', round(User::withCount('products')->get()->avg('products_count'), 2)),
            Stat::make('Max Products/User', User::withCount('products')->get()->max('products_count')),
        ];
    }
}
