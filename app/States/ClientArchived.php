<?php

declare(strict_types=1);

namespace App\States;

class ClientArchived extends ClientState
{
    public function actionText(): string
    {
        return 'Archive';
    }

    public static function name(): string
    {
        return 'archived';
    }

    public function color(): string
    {
        return 'danger';
    }
}
