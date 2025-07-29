<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Product;

class ProductsCreatedChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $trend = Trend::model(Product::class)
            ->between(now()->subYear(), now())
            ->perWeek()
            ->count();

        return [
            'labels'   => $trend->map(fn (TrendValue $v) => $v->date),
            'datasets' => [[
                'label' => 'New Products',
                'data'  => $trend->map(fn ($v) => $v->aggregate),
                'backgroundColor' => '#3B82F6',
                'borderColor'     => '#2563EB',
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
