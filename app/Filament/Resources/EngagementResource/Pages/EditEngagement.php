<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementResource\Pages;

use App\Filament\Resources\EngagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngagement extends EditRecord
{
    protected static string $resource = EngagementResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalName = $this->record->getOriginal('name');
        $newName = $data['name'];

        // If name changed, recalculate version for the new name + client_fk combo
        if ($originalName !== $newName) {
            $maxVersion = \App\Models\Engagement::where('client_fk', $data['client_fk'])
                ->where('name', $newName)
                ->where('id', '!=', $this->record->id) // Exclude current record
                ->max('version');

            $data['version'] = $maxVersion ? $maxVersion + 1 : 1;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Refresh the record and refill the form to show updated values (e.g., recalculated version)
        $this->record->refresh();
        $this->fillForm();
    }
}
