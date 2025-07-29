<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopUsersByProducts extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->withCount('products')
                    ->has('products')
                    ->orderByDesc('products_count')
                    ->take(5)
            )
            ->columns([
                TextColumn::make('full_name')->label('Name'),
                TextColumn::make('products_count')->label('Listings'),
            ])
            ->paginated(false);
    }
}
