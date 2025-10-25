<?php

declare(strict_types=1);

namespace App\States;

class EngagementCompleted extends EngagementState
{
    public static function name(): string
    {
        return 'completed';
    }

    public function color(): string
    {
        return 'success';
    }
}
