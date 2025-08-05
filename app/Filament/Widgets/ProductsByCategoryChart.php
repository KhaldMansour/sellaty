<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Category;

class ProductsByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Products by Category';

    protected int|string|array $columnSpan = 1;


    protected function getData(): array
    {
        $cats = Category::withCount('products')->get();

        return [
            'labels' => $cats->pluck('name_en')->toArray(),
            'datasets' => [[
                'label' => 'Count',
                'data' => $cats->pluck('products_count')->toArray(),
                'backgroundColor' => ['#F87171','#34D399','#60A5FA','#FBBF24'],
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
