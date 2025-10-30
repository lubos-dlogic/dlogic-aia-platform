<?php

declare(strict_types=1);

namespace App\States;

class EngagementActive extends EngagementState
{
    public function actionText(): string
    {
        return 'Activate';
    }

    public static function name(): string
    {
        return 'active';
    }

    public function color(): string
    {
        return 'info';
    }
}
