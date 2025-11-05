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
            ->allowTransition(EngagementOnHold::class, EngagementPlanning::class)
            ->allowTransition(EngagementOnHold::class, EngagementActive::class)
            ->allowTransition(EngagementOnHold::class, EngagementCancelled::class)
            ->allowTransition(EngagementOnHold::class, EngagementCompleted::class)
            ->allowTransition(EngagementCompleted::class, EngagementActive::class)
            ->allowTransition(EngagementCompleted::class, EngagementCancelled::class)
            ->allowTransition(EngagementCompleted::class, EngagementOnHold::class)
            ->allowTransition(EngagementCancelled::class, EngagementOnHold::class)
            ->allowTransition(EngagementCancelled::class, EngagementActive::class);
    }
}
