<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementAuditResource\Pages;

use App\Filament\Resources\EngagementAuditResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEngagementAudit extends CreateRecord
{
    protected static string $resource = EngagementAuditResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user'] = Auth::id();

        return $data;
    }
}
