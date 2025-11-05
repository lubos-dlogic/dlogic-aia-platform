<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessIdentified extends EngagementProcessState
{
    public static function name(): string
    {
        return 'identified';
    }

    public function color(): string
    {
        return 'gray';
    }
}
