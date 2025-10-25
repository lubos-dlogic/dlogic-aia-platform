<?php

declare(strict_types=1);

namespace App\States;

class ClientDraft extends ClientState
{
    public static function name(): string
    {
        return 'draft';
    }

    public function color(): string
    {
        return 'gray';
    }
}
