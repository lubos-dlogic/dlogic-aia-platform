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
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->maxLength(12)
                            ->hidden(fn (string $operation) => $operation === 'create')
                            ->readOnly(fn (string $operation) => $operation === 'edit'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Select::make('client_fk')
                            ->label('Client')
                            ->relationship('client', 'name') // assumes Engagement::client() belongsTo(Client::class, 'client_fk')
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Client::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('clinet_key', 'like', "%{$search}%")
                                    ->limit(10)
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => \App\Models\Client::find($value)?->name)
                            ->required(),
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
                    ]),
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
