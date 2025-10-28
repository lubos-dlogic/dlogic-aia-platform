<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function transitionState(int $id, string $target): void
    {
        $client = Client::find($id);

        if (! $client || ! class_exists($target)) {
            return;
        }

        try {
            $client->state->transitionTo($target);
            $client->refresh();

            Notification::make()
                ->title('Status changed to ' . ucfirst((new $target($client))->name()))
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
