<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalRegisteredUsers extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
            Stat::make('New This Week', User::whereBetween('created_at', [now()->startOfWeek(), now()])->count()),
            Stat::make('New This Month', User::whereMonth('created_at', now()->month)->count()),
            Stat::make('Verified Users', User::where('is_verified', true)->count()),
        ];
    }
}
