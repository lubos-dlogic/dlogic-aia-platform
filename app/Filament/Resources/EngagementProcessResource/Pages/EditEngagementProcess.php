<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementProcessResource\Pages;

use App\Filament\Resources\EngagementProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEngagementProcess extends EditRecord
{
    protected static string $resource = EngagementProcessResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
