<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                ->disabled(),
            Forms\Components\TextInput::make('last_name')
                ->disabled(),
            Forms\Components\TextInput::make('username')
                ->disabled(),
            Forms\Components\TextInput::make('email')
                ->disabled(),
            Forms\Components\TextInput::make('phone_number')
                ->disabled(),
            Placeholder::make('current_image')
                ->label('Image Preview')
                ->content(fn ($record) => new HtmlString(
                    $record->profile_photo
                        ? '<img src="' . url($record->profile_photo) . '" class="w-32 h-32 rounded-full" />'
                        : '<span>No image uploaded</span>'
                )),

            Forms\Components\Toggle::make('locked')
                ->label('Locked')
                ->required(),

            Forms\Components\Toggle::make('is_verified')
                ->label('Verified')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('profile_photo')->label('Profile Photo')->circular(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                    Tables\Columns\ToggleColumn::make('locked')
                    ->label('Locked'),
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label('Verified'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => !$record->hasRole(User::ROLE_SUPER_ADMIN))
                    ->before(function ($record, $action) {
                        if ($record->hasRole(User::ROLE_SUPER_ADMIN)) {
                            throw new \Exception("You cannot delete a Super Admin user.");
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records, $action) {
                            foreach ($records as $record) {
                                if ($record->hasRole(User::ROLE_SUPER_ADMIN)) {
                                    throw new \Exception("Cannot delete users with Super Admin role.");
                                }
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
