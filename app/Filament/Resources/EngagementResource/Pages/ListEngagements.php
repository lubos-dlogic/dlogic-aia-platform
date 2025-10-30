<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementResource\Pages;

use App\Filament\Resources\EngagementResource;
use App\Models\Engagement;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEngagements extends ListRecords
{
    protected static string $resource = EngagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function transitionState(int $id, string $target): void
    {
        $engagement = Engagement::find($id);

        if (! $engagement || ! class_exists($target)) {
            return;
        }

        try {
            $engagement->state->transitionTo($target);
            $engagement->refresh();

            Notification::make()
                ->title('Status changed to ' . ucfirst((new $target($engagement))->name()))
                ->success()
                ->send();

            $this->dispatch('$refresh'); // refresh table
            $this->dispatch('close-modal', id: 'changeState'); // close modal
        } catch (\Throwable $e) {
            Notification::make()
                ->title('State change failed')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function getMaxContentWidth(): ?string
    {
        // 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl',
        // 'full', 'screen-sm', 'screen-md', 'screen-lg', 'screen-xl', 'screen-2xl'
        return 'full';
    }
}
