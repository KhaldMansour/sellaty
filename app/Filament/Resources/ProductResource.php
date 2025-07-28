<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Concerns\Translatable;

class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getTranslatableLocales(): array
    {
        return ['en', 'ar'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\TextInput::make('description'),

            Forms\Components\TextInput::make('price')
                ->required()
                ->numeric()
                ->disabled(),

            Forms\Components\TextInput::make('duration')
                ->required()
                ->maxLength(255)
                ->disabled(),

            Forms\Components\TextInput::make('quantity')
                ->required()
                ->numeric(),

            Forms\Components\TextInput::make('condition')
                ->required()
                ->disabled(),

            Forms\Components\TextInput::make('delivery_options')
                ->required()
                ->disabled(),

            Forms\Components\TextInput::make('city')
                ->required()
                ->maxLength(255)
                ->disabled(),

            Forms\Components\DatePicker::make('listed_until')
                ->required(),

                Forms\Components\Select::make('status')
                ->required()
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active'),

            Forms\Components\TextInput::make('user_id')
                ->required()
                ->numeric()
                ->disabled(),

            Forms\Components\Toggle::make('featured')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(true, function ($query, $search) {
                        return $query->whereRaw("MATCH(name_en, name_ar) AGAINST (? IN BOOLEAN MODE)", [$search . '*']);
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('listed_until')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
