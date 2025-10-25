<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessBeingAnalysed extends EngagementProcessState
{
    public static function name(): string
    {
        return 'being_analysed';
    }

    public function color(): string
    {
        return 'info';
    }
}
