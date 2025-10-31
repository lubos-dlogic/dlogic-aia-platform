<?php

declare(strict_types=1);

namespace App\States;

class EngagementAuditScheduled extends EngagementAuditState
{
    public function actionText(): string
    {
        return 'Schedule';
    }

    public static function name(): string
    {
        return 'scheduled';
    }

    public function color(): string
    {
        return 'gray';
    }
}
