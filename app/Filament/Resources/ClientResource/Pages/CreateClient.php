<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    public static string|\Filament\Support\Enums\Alignment $formActionsAlignment = 'right';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user'] = Auth::id();

        if (! empty($data['client_key'])) {
            $data['client_key'] = Str::upper($data['client_key']);
        }

        if (! empty($data['country'])) {
            $data['country'] = Str::upper($data['country']);
        }

        return $data;
    }
}
