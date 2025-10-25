<?php

declare(strict_types=1);

namespace App\States;

class EngagementAuditInProgress extends EngagementAuditState
{
    public static function name(): string
    {
        return 'in_progress';
    }

    public function color(): string
    {
        return 'info';
    }
}
