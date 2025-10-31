<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Filament\Resources\ClientResource\Widgets\ClientActivityTimeline;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Refresh the record to show updated values (e.g., recalculated version)
        $this->record->refresh();
        $this->fillForm();

        // Refresh the footer widgets (e.g., Activity Timeline)
        $this->dispatch('refreshActivityTimeline');
    }

    protected function getFooterWidgets(): array
    {
        return [
            ClientActivityTimeline::class,
        ];
    }
}
