<?php

declare(strict_types=1);

namespace App\States;

class ClientInactive extends ClientState
{
    public static function name(): string
    {
        return 'inactive';
    }

    public function color(): string
    {
        return 'warning';
    }
}
