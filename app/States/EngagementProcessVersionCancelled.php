<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessVersionCancelled extends EngagementProcessVersionState
{
    public static function name(): string
    {
        return 'cancelled';
    }

    public function color(): string
    {
        return 'danger';
    }
}
