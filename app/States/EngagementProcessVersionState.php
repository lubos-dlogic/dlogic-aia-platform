<?php

declare(strict_types=1);

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EngagementProcessVersionState extends State
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EngagementProcessVersionDraft::class)
            ->allowTransition(EngagementProcessVersionDraft::class, EngagementProcessVersionActive::class)
            ->allowTransition(EngagementProcessVersionDraft::class, EngagementProcessVersionCancelled::class)
            ->allowTransition(EngagementProcessVersionActive::class, EngagementProcessVersionCancelled::class)
            ->allowTransition(EngagementProcessVersionCancelled::class, EngagementProcessVersionArchived::class);
    }
}
