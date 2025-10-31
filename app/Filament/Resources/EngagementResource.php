<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EngagementResource\Pages;
use App\Models\Engagement;
use App\States\EngagementActive;
use App\States\EngagementCancelled;
use App\States\EngagementCompleted;
use App\States\EngagementOnHold;
use App\States\EngagementPlanning;
use App\States\EngagementState;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use InvadersXX\FilamentJsoneditor\Forms\JSONEditor;

class EngagementResource extends Resource
{
    protected static ?string $model = Engagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Client Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Engagement Information')
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
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                if (trim($search) === '') {
                                    return collect(); // nothing shown when search empty
                                }

                                $clients = \App\Models\Client::query()
                                    ->where(function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhere('client_key', 'like', "%{$search}%");
                                    })
                                    ->limit(10)
                                    ->get();

                                return $clients->isEmpty()
                                    ? collect() // return empty => no matches shown
                                    : $clients->mapWithKeys(fn ($c) => [
                                        $c->id => "{$c->name} ({$c->client_key})",
                                    ]);
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $client = \App\Models\Client::find($value);

                                return $client ? "{$client->name} ({$client->client_key})" : null;
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->client_key})")
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->helperText('Cannot be changed after record is created'),
                        Forms\Components\TextInput::make('version')
                            ->numeric()
                            ->hidden(fn (string $operation) => $operation === 'create')
                            ->readOnly(fn (string $operation) => $operation === 'edit'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        JSONEditor::make('data')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status & Actions')
                    ->schema(function ($record) {
                        if (! $record) {
                            return [
                                Forms\Components\Placeholder::make('state_placeholder')
                                    ->label('Status')
                                    ->content('Planning'),
                            ];
                        }

                        $current = $record->state;
                        $currentName = ucfirst($current->name());
                        $currentColor = $current->color();

                        // Get all allowed target states
                        $allowedTargets = $current->transitionableStates();

                        // Build transition buttons
                        $buttons = collect($allowedTargets)->map(function ($target) use ($record) {
                            /**
                             * @var EngagementState $targetState
                             */
                            $targetState = new $target($record);
                            $label = $targetState->actionText();
                            $color = $targetState->color();

                            return Forms\Components\Actions\Action::make("to_{$label}")
                                ->label($label)
                                ->color($color)
                                ->requiresConfirmation()
                                ->action(function ($livewire) use ($record, $target) {
                                    $record->state->transitionTo($target);
                                    $livewire->dispatch('refreshActivityTimeline');
                                })
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

                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record) => $record?->created_at?->format('M d, Y g:i A') ?? 'n/a'),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Updated At')
                            ->content(fn ($record) => $record?->updated_at?->format('M d, Y g:i A') ?? 'n/a'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client_fk')
                    ->label('Client')
                    ->formatStateUsing(
                        fn ($record) => $record->client
                        ? "{$record->client->name} ({$record->client->client_key})"
                        : 'N/A',
                    )
                    ->searchable(['name', 'client_key'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('state')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state->name()) : 'Unknown')
                    ->color(fn ($state) => $state ? $state->color() : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('version')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('System'),

                Tables\Columns\TextColumn::make('created_by_process')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('Status')
                    ->options([
                        EngagementPlanning::class => 'Planning',
                        EngagementActive::class => 'Active',
                        EngagementOnHold::class => 'On Hold',
                        EngagementCancelled::class => 'Cancelled',
                        EngagementCompleted::class => 'Completed',
                    ]),

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

                Tables\Actions\EditAction::make(),
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
