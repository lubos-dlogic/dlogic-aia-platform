<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EngagementResource\Pages;
use App\Models\Engagement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EngagementResource extends Resource
{
    protected static ?string $model = Engagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(12),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('client_fk')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('version')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('description')
                    ->maxLength(1000),
                Forms\Components\TextInput::make('data'),
                Forms\Components\TextInput::make('state')
                    ->required()
                    ->maxLength(255)
                    ->default('planning'),
                Forms\Components\TextInput::make('created_by_user')
                    ->numeric(),
                Forms\Components\TextInput::make('created_by_process')
                    ->maxLength(100),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_fk')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_by_user')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by_process')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEngagements::route('/'),
            'create' => Pages\CreateEngagement::route('/create'),
            'edit' => Pages\EditEngagement::route('/{record}/edit'),
        ];
    }
}
