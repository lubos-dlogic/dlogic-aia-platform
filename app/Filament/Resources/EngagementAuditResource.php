<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Filament\Resources\EngagementAuditResource\Pages;
use App\Models\EngagementAudit;
use App\Services\ColorMappingService;
use App\States\EngagementAuditCancelled;
use App\States\EngagementAuditCompleted;
use App\States\EngagementAuditDraft;
use App\States\EngagementAuditInProgress;
use App\States\EngagementAuditScheduled;
use App\States\EngagementAuditState;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use InvadersXX\FilamentJsoneditor\Forms\JSONEditor;

class EngagementAuditResource extends Resource
{
    protected static ?string $model = EngagementAudit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Client Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Audit Information')
                    ->schema([
                        Forms\Components\Select::make('engagement_fk')
                            ->label('Engagement')
                            ->relationship('engagement', 'name')
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                if (trim($search) === '') {
                                    return collect();
                                }

                                $engagements = \App\Models\Engagement::query()
                                    ->where(function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                            ->orWhere('key', 'like', "%{$search}%");
                                    })
                                    ->limit(10)
                                    ->get();

                                return $engagements->isEmpty()
                                    ? collect()
                                    : $engagements->mapWithKeys(fn ($e) => [
                                        $e->id => "{$e->name} ({$e->key})",
                                    ]);
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $engagement = \App\Models\Engagement::find($value);

                                return $engagement ? "{$engagement->name} ({$engagement->key})" : null;
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->key})")
                            ->required()
                            ->disabled(fn (string $operation) => $operation === 'edit')
                            ->helperText('Cannot be changed after record is created'),

                        Forms\Components\Placeholder::make('client_info')
                            ->label('Client')
                            ->content(fn ($record) => $record?->engagement?->client
                                ? "{$record->engagement->client->name} ({$record->engagement->client->client_key})"
                                : 'n/a')
                            ->visibleOn('edit'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Select::make('type')
                            ->required()
                            ->options(ColorMappingService::getAuditTypeOptions())
                            ->searchable()
                            ->native(false),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        JSONEditor::make('data')
                            ->label('Audit Data (JSON)')
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
                        $currentName = ucfirst(str_replace('_', ' ', $current->name()));
                        $currentColor = $current->color();

                        // Get all allowed target states
                        $allowedTargets = $current->transitionableStates();

                        // Build transition buttons
                        $buttons = collect($allowedTargets)->map(function ($target) use ($record) {
                            /**
                             * @var EngagementAuditState $targetState
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
                Tables\Columns\TextColumn::make('engagement_fk')
                    ->label('Engagement')
                    ->formatStateUsing(
                        fn ($record) => $record->engagement
                        ? "{$record->engagement->name} ({$record->engagement->key})"
                        : 'N/A',
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('engagement.client_fk')
                    ->label('Client')
                    ->formatStateUsing(
                        fn ($record) => $record->engagement?->client
                        ? "{$record->engagement->client->name} ({$record->engagement->client->client_key})"
                        : 'N/A',
                    )
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('engagement.client', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('client_key', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ColorMappingService::getAuditTypeLabel($state) : 'N/A')
                    ->color(fn ($state) => $state ? ColorMappingService::getAuditTypeColor($state) : 'gray'),

                Tables\Columns\TextColumn::make('state')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state->name())) : 'Unknown')
                    ->color(fn ($state) => $state ? $state->color() : 'gray')
                    ->sortable(),

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
                        EngagementAuditDraft::class => 'Draft',
                        EngagementAuditScheduled::class => 'Scheduled',
                        EngagementAuditInProgress::class => 'In Progress',
                        EngagementAuditCompleted::class => 'Completed',
                        EngagementAuditCancelled::class => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('type')
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

                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    FilamentExportBulkAction::make('export'),
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
            'index' => Pages\ListEngagementAudits::route('/'),
            'create' => Pages\CreateEngagementAudit::route('/create'),
            'edit' => Pages\EditEngagementAudit::route('/{record}/edit'),
        ];
    }
}
