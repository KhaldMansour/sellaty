<?php

namespace App\Filament\Resources\IntroMessageResource\Pages;

use App\Filament\Resources\IntroMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntroMessage extends EditRecord
{
    protected static string $resource = IntroMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
