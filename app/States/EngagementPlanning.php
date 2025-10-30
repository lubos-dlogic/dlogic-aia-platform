<?php

declare(strict_types=1);

namespace App\States;

class EngagementPlanning extends EngagementState
{
    public function actionText(): string
    {
        return 'Revert to Planning';
    }

    public static function name(): string
    {
        return 'planning';
    }

    public function color(): string
    {
        return 'gray';
    }
}
