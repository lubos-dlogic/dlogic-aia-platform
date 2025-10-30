<?php

declare(strict_types=1);

namespace App\States;

class EngagementOnHold extends EngagementState
{
    public function actionText(): string
    {
        return 'Put On Hold';
    }

    public static function name(): string
    {
        return 'on hold';
    }

    public function color(): string
    {
        return 'gray';
    }
}
