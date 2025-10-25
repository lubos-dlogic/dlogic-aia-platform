<?php

declare(strict_types=1);

namespace App\States;

class EngagementProcessAnalysedAndMapped extends EngagementProcessState
{
    public static function name(): string
    {
        return 'analysed_and_mapped';
    }

    public function color(): string
    {
        return 'success';
    }
}
