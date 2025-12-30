<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // // TextInput::make('phone_sensitive_id')
                // //     ->tel()
                // //     ->required()
                // //     ->numeric(),
                // TextInput::make('first_name')
                //     ->required(),
                // TextInput::make('last_name')
                //     ->required(),
                // DatePicker::make('birth_date')
                //     ->required(),
                // TextInput::make('legal_doc_url')
                //     // ->url()
                //     ->required(),
                // TextInput::make('legal_photo_url')
                //     // ->url()
                //     ->required(),
                // // TextInput::make('password')
                //     ->password()
                //     ->required(),
                Toggle::make('is_phone_number_validated')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_admin_validated'),
            ]);
    }
}
