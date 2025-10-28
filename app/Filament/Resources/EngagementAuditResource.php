<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EngagementAuditResource\Pages;
use App\Models\EngagementAudit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EngagementAuditResource extends Resource
{
    protected static ?string $model = EngagementAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('engagement_fk')
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('data'),
                Forms\Components\TextInput::make('description')
                    ->maxLength(1000),
                Forms\Components\TextInput::make('state')
                    ->required()
                    ->maxLength(255)
                    ->default('scheduled'),
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
                Tables\Columns\TextColumn::make('engagement_fk')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
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
            'index' => Pages\ListEngagementAudits::route('/'),
            'create' => Pages\CreateEngagementAudit::route('/create'),
            'edit' => Pages\EditEngagementAudit::route('/{record}/edit'),
        ];
    }
}
