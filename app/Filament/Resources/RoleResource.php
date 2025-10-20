<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn (?Role $record): bool => $record?->isSystemRole() ?? false)
                            ->helperText(fn (?Role $record): ?string => $record?->isSystemRole() ? 'System roles cannot be renamed.' : null)
                            ->autofocus(),
                        Forms\Components\Select::make('guard_name')
                            ->label('Guard')
                            ->options([
                                'web' => 'Web',
                                'api' => 'API',
                            ])
                            ->default('web')
                            ->required()
                            ->helperText('Select the guard this role applies to.'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Describe what this role is for and what permissions it should have.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->gridDirection('row')
                            ->label('Assign Permissions')
                            ->helperText('Select the permissions this role should have.')
                            ->options(function () {
                                return Permission::query()
                                    ->pluck('name', 'id')
                                    ->map(fn ($name) => ucwords(str_replace(['_', 'any'], [' ', 'Any'], $name)));
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Role $record): string => match (true) {
                        $record->name === 'super_admin' => 'danger',
                        $record->name === 'admin' => 'warning',
                        $record->name === 'user' => 'success',
                        default => 'info',
                    })
                    ->icon(fn (Role $record): string => $record->isSystemRole() ? 'heroicon-o-lock-closed' : 'heroicon-o-shield-check')
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->badge()
                    ->color('success')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('permissions')->orderBy('permissions_count', $direction);
                    }),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->badge()
                    ->color('info')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('users')->orderBy('users_count', $direction);
                    }),
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
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
                Tables\Filters\Filter::make('has_users')
                    ->query(fn (Builder $query): Builder => $query->has('users'))
                    ->label('Has Users')
                    ->toggle(),
                Tables\Filters\Filter::make('system_roles')
                    ->query(fn (Builder $query): Builder => $query->whereIn('name', Role::SYSTEM_ROLES))
                    ->label('System Roles Only')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_permissions')
                    ->label('View Permissions')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Role $record): string => 'Permissions for ' . $record->display_name)
                    ->modalContent(fn (Role $record) => view('filament.resources.role-resource.view-permissions', ['role' => $record]))
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Duplicate Role')
                    ->modalDescription(fn (Role $record): string => 'Create a copy of "' . $record->display_name . '" with all its permissions?')
                    ->action(function (Role $record): void {
                        $newRole = $record->replicate(['name']);
                        $newRole->name = $record->name . '_copy';
                        $newRole->description = 'Copy of ' . $record->description;
                        $newRole->save();
                        $newRole->syncPermissions($record->permissions);

                        \Filament\Notifications\Notification::make()
                            ->title('Role Duplicated')
                            ->success()
                            ->body('The role has been duplicated successfully.')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Role $record): bool => !$record->isSystemRole())
                    ->modalDescription('Are you sure you want to delete this role? Users with this role will lose their permissions.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records->reject(fn (Role $role) => $role->isSystemRole())->each->delete();
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
