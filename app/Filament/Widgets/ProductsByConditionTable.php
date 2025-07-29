<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductsByConditionTable extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->selectRaw("
                        JSON_UNQUOTE(`condition`->>'$[0]') as condition_value,
                        COUNT(*) as total,
                        MIN(id) as id_key
                    ")
                    ->groupBy('condition_value')
                    ->orderByDesc('total')
            )
            ->columns([
                TextColumn::make('condition_value')->label('Condition'),
                TextColumn::make('total')->label('Count'),
            ])
            ->paginated(false);
    }

    public function getTableRecordKey($record) :string
    {
        return (string) $record->condition_value;
    }
}
