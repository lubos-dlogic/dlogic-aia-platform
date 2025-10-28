<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementProcessResource\Pages;

use App\Filament\Resources\EngagementProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEngagementProcesses extends ListRecords
{
    protected static string $resource = EngagementProcessResource::class;

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
