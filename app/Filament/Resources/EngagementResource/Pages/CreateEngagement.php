<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementResource\Pages;

use App\Filament\Resources\EngagementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEngagement extends CreateRecord
{
    protected static string $resource = EngagementResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $today = now()->format('ymd');
        $countToday = \App\Models\Engagement::whereDate('created_at', now())->count() + 1;
        $data['key'] = sprintf('EG%sX%02d', $today, $countToday);

        return $data;
    }
}
