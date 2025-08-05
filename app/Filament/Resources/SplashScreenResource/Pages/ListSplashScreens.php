<?php

namespace App\Filament\Resources\SplashScreenResource\Pages;

use App\Filament\Resources\SplashScreenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSplashScreens extends ListRecords
{
    protected static string $resource = SplashScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
