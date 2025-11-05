<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementProcessResource\Pages;

use App\Filament\Resources\EngagementProcessResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEngagementProcess extends CreateRecord
{
    protected static string $resource = EngagementProcessResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';
}
