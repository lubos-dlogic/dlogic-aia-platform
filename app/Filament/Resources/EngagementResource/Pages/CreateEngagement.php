<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementResource\Pages;

use App\Filament\Resources\EngagementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEngagement extends CreateRecord
{
    protected static string $resource = EngagementResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user'] = Auth::id();

        // Auto-calculate version based on existing engagements with same client_fk and name
        $maxVersion = \App\Models\Engagement::where('client_fk', $data['client_fk'])
            ->where('name', $data['name'])
            ->max('version');

        $data['version'] = $maxVersion ? $maxVersion + 1 : 1;

        $today = now()->format('ymd');
        $countToday = \App\Models\Engagement::whereDate('created_at', now())->count() + 1;
        $data['key'] = sprintf('EG%sX%02d', $today, $countToday);

        return $data;
    }
}
