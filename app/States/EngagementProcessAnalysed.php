<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessAnalysed extends EngagementProcessState
{
    public static function name(): string
    {
        return 'analysed';
    }

    public function color(): string
    {
        return 'success';
    }
}
