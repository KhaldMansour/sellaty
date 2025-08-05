<?php

namespace App\Filament\Resources\IntroMessageResource\Pages;

use App\Filament\Resources\IntroMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIntroMessages extends ListRecords
{
    protected static string $resource = IntroMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
