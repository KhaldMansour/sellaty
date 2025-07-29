<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProductResource\Widgets\ProductStatsOverview;
use Filament\Pages\Dashboard as BasePage;
use App\Filament\Resources\UserResource\Widgets\UsersChart;
use App\Filament\Widgets\ProductsByCategoryChart;
use App\Filament\Widgets\ProductsCreatedChart;

class Dashboard extends BasePage
{
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [
            ProductStatsOverview::class,
            ProductsCreatedChart::class,
            UsersChart::class,
            ProductsByCategoryChart::class,
            \App\Filament\Resources\UserResource\Widgets\TotalRegisteredUsers::class,
            \App\Filament\Resources\UserResource\Widgets\TopUsersByProducts::class,
            \App\Filament\Resources\UserResource\Widgets\TopProductsByOffers::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
