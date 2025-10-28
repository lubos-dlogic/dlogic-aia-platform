<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementAuditResource\Pages;

use App\Filament\Resources\EngagementAuditResource;
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
}
