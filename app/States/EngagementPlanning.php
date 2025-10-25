<?php

declare(strict_types=1);

namespace App\States;

class EngagementPlanning extends EngagementState
{
    public static function name(): string
    {
        return 'planning';
    }

    public function color(): string
    {
        return 'gray';
    }
}
