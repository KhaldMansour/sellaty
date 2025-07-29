<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsByOffers extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->withCount('offers')
                    ->has('offers')
                    ->orderByDesc('offers_count')
                    ->take(5)
            )
            ->columns([
                TextColumn::make('name_en')->label('Name'),
                TextColumn::make('offers_count')->label('offers'),
            ])
            ->paginated(false); 
    }
}
