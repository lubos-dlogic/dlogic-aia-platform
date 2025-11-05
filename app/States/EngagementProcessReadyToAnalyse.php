<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessReadyToAnalyse extends EngagementProcessState
{
    public static function name(): string
    {
        return 'ready_to_analyse';
    }

    public function color(): string
    {
        return 'warning';
    }
}
