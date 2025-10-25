<?php

declare(strict_types=1);

namespace App\States;

class ClientActive extends ClientState
{
    public static function name(): string
    {
        return 'active';
    }

    public function color(): string
    {
        return 'success';
    }
}
