<?php

declare(strict_types=1);

namespace App\States;

class EngagementAuditCompleted extends EngagementAuditState
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
