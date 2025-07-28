<?php

namespace App\Filament\Resources\WantedProductResource\Pages;

use App\Filament\Resources\WantedProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\Translatable;

class EditWantedProduct extends EditRecord
{
    use Translatable;

    protected static string $resource = WantedProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
