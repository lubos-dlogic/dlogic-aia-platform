<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\States\ClientActive;
use App\States\ClientArchived;
use App\States\ClientDraft;
use App\States\ClientInactive;
use App\States\ClientState;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Client Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('client_key')
                            ->label('Client Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(30)
                            ->alphaDash()
                            ->helperText('Unique identifier for this client'),

                        Forms\Components\Select::make('country')
                            ->required()
                            ->searchable()
                            ->options([
                                'NL' => 'Netherlands (NL)',
                                'GB' => 'United Kingdom (GB)',
                                'SK' => 'Slovakia (SK)',
                                'DE' => 'Germany (DE)',
                                'BE' => 'Belgium (BE)',
                                'IE' => 'Ireland (IE)',
                                'CZ' => 'Czech Republic (CZ)',
                                'PL' => 'Poland (PL)',
                                'FR' => 'France (FR)',
                                'DK' => 'Denmark (DK)',
                            ])
                            ->helperText('Select country'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Contact & Company Details')
                    ->schema([
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(250)
                            ->placeholder('https://example.com')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('company_gid')
                            ->label('Company Registration ID')
                            ->maxLength(40),

                        Forms\Components\TextInput::make('company_vat_gid')
                            ->label('VAT/Tax ID')
                            ->maxLength(40),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status & Actions')
                    ->schema(function ($record) {
                        if (! $record) {
                            return [
                                Forms\Components\Placeholder::make('state_placeholder')
                                    ->label('Status')
                                    ->content('Draft'),
                            ];
                        }

                        $current = $record->state;
                        $currentName = ucfirst($current->name());
                        $currentColor = $current->color();

                        // ✅ Get all allowed target states
                        $allowedTargets = $current->transitionableStates();

                        // Build transition buttons
                        $buttons = collect($allowedTargets)->map(function ($target) use ($record) {
                            /**
                             * @var ClientState $targetState
                             */
                            $targetState = new $target($record);
                            $label = $targetState->actionText();
                            $color = $targetState->color();

                            return Forms\Components\Actions\Action::make("to_{$label}")
                                ->label($label)
                                ->color($color)
                                ->requiresConfirmation()
                                ->action(fn () => $record->state->transitionTo($target))
                                ->disabled(! auth()->user()->can('changeState', $record));
                        })->all();

                        return [
                            Forms\Components\Placeholder::make('current_state')
                                ->label('Current Status')
                                ->content($currentName)
                                ->extraAttributes(['class' => "font-semibold text-{$currentColor}-500"]),

                            Forms\Components\Actions::make($buttons),
                        ];
                    })
                    ->visible(fn ($record) => $record !== null),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\Placeholder::make('created_by_user')
                            ->label('Created By')
                            ->content(
                                fn ($record) =>
                            $record?->creator
                                ? "{$record->creator->name} - {$record->creator->email}"
                                : 'n/a',
                            ),

                        Forms\Components\Placeholder::make('created_by_process')
                            ->label('Created By Process')
                            ->content(fn ($record) => $record?->created_by_process ?? 'n/a'),
                    ])
                    ->columns(1)
                    ->visibleOn('edit')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client_key')
                    ->label('Client Key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $countries = [
                            'NL' => 'Netherlands',
                            'GB' => 'United Kingdom',
                            'SK' => 'Slovakia',
                            'DE' => 'Germany',
                            'BE' => 'Belgium',
                            'IE' => 'Ireland',
                            'CZ' => 'Czech Republic',
                            'PL' => 'Poland',
                            'FR' => 'France',
                            'DK' => 'Denmark',
                        ];

                        return $countries[$state] ?? $state;
                    }),

                Tables\Columns\TextColumn::make('state')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state->name()) : 'Unknown')
                    ->color(fn ($state) => $state ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('website')
                    ->searchable()
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('Status')
                    ->options([
                        ClientDraft::class => 'Draft',
                        ClientActive::class => 'Active',
                        ClientInactive::class => 'Inactive',
                        ClientArchived::class => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->multiple(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created from'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('changeState')
                    ->label('Change Status')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn ($record) => auth()->user()->can('changeState', $record))
                    ->modalHeading(fn ($record) => 'Change Status – ' . ($record->name ?? ''))
                    ->modalWidth('md')
                    ->modalContent(fn ($record) => view('filament.components.state-actions', [
                        'record' => $record,
                        'allowed' => $record->state->transitionableStates(),
                    ]))
                    ->modalFooterActions([])
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->color('secondary')
                    ->requiresConfirmation(true)
                    ->action(fn () => null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Add relationship managers here when needed
            // e.g., RelationManagers\EngagementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
