<?php

declare(strict_types=1);

namespace App\States;

class EngagementAuditCancelled extends EngagementAuditState
{
    public function actionText(): string
    {
        return 'Cancel';
    }

    public static function name(): string
    {
        return 'cancelled';
    }

    public function color(): string
    {
        return 'danger';
    }
}
