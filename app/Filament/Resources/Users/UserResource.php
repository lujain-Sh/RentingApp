<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Dom\Text;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'user';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
{
    return $table->recordUrl(null) 
    
    ->defaultSort(fn ($query) => $query->orderByRaw("
            CASE 
                WHEN is_admin_validated IS NULL THEN 0 
                WHEN is_admin_validated = 0 THEN 1 
                ELSE 2 
            END ASC
        "))

    ->columns([
        ImageColumn::make('legal_photo_url')
            ->label('personal photo')
            ->disk('public') 
            ->circular() 
            ->alignCenter()
            ->size(60, 60)
            ->url(fn ($record) => asset('storage/' . $record->legal_photo_url))
            ->openUrlInNewTab(),

        TextColumn::make('first_name')
            ->label('first name')
            ->alignCenter()
            ->searchable(),

        TextColumn::make('last_name')
            ->label('last name')
            ->alignCenter()
            ->searchable(),
        
        TextColumn::make('full_phone_str')
            ->label('phone number')
            ->alignCenter()
            ->searchable(),

        TextColumn::make('birth_date')
            ->label('birth date')
            ->alignCenter()
            ->date(),

        ImageColumn::make('legal_doc_url')
            ->label('legal document')
            ->disk('public')
            ->alignCenter()
            ->size(60, 40)
            ->url(fn ($record) => asset('storage/' . $record->legal_doc_url))
            ->openUrlInNewTab(),

        IconColumn::make('is_active')
            ->label('is active')
            ->alignCenter()
            ->boolean(),
        IconColumn::make('is_admin_validated')
            ->label('is admin validated')
            ->alignCenter()
            ->boolean(),
    ])
    ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ])
    ;
}
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
