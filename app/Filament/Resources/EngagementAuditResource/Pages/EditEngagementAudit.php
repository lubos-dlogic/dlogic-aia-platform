<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementAuditResource\Pages;

use App\Filament\Resources\EngagementAuditResource;
use App\Filament\Resources\EngagementAuditResource\Widgets\EngagementAuditActivityTimeline;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngagementAudit extends EditRecord
{
    protected static string $resource = EngagementAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Refresh the record to show updated values
        $this->record->refresh();
        $this->fillForm();

        // Refresh the footer widgets (e.g., Activity Timeline)
        $this->dispatch('refreshActivityTimeline');
    }

    protected function getFooterWidgets(): array
    {
        return [
            EngagementAuditActivityTimeline::class,
        ];
    }
}
