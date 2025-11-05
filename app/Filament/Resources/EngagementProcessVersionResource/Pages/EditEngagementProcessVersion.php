<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementProcessVersionResource\Pages;

use App\Filament\Resources\EngagementProcessVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngagementProcessVersion extends EditRecord
{
    protected static string $resource = EngagementProcessVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
