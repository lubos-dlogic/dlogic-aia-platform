<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementAuditResource\Pages;

use App\Filament\Resources\EngagementAuditResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEngagementAudits extends ListRecords
{
    protected static string $resource = EngagementAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getMaxContentWidth(): ?string
    {
        // 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl',
        // 'full', 'screen-sm', 'screen-md', 'screen-lg', 'screen-xl', 'screen-2xl'
        return 'full';
    }
}
