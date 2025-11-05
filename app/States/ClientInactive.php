<?php

declare(strict_types=1);

namespace App\States;

class ClientInactive extends ClientState
{
    public function actionText(): string
    {
        return 'Inactivate';
    }

    public static function name(): string
    {
        return 'inactive';
    }

    public function color(): string
    {
        return 'warning';
    }
}
