<?php

namespace App\Filament\Resources\WantedProductResource\Pages;

use App\Filament\Resources\WantedProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;

class ListWantedProducts extends ListRecords
{
    use Translatable;

    protected static string $resource = WantedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
