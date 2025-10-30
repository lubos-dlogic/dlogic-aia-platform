<?php

declare(strict_types=1);

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EngagementState extends State
{
    abstract public function actionText(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EngagementPlanning::class)
            ->allowTransition(EngagementPlanning::class, EngagementActive::class)
            ->allowTransition(EngagementPlanning::class, EngagementCancelled::class)
            ->allowTransition(EngagementPlanning::class, EngagementOnHold::class)
            ->allowTransition(EngagementPlanning::class, EngagementCompleted::class)
            ->allowTransition(EngagementActive::class, EngagementCompleted::class)
            ->allowTransition(EngagementActive::class, EngagementOnHold::class)
            ->allowTransition(EngagementActive::class, EngagementCancelled::class)
            ->allowTransition(EngagementCancelled::class, EngagementOnHold::class)
            ->allowTransition(EngagementCancelled::class, EngagementActive::class);
    }
}
