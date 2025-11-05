<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessBeingDescribed extends EngagementProcessState
{
    public static function name(): string
    {
        return 'being_described';
    }

    public function color(): string
    {
        return 'info';
    }
}
