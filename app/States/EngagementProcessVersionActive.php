<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessVersionActive extends EngagementProcessVersionState
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
