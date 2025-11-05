<?php

declare(strict_types=1);

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EngagementProcessState extends State
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EngagementProcessIdentified::class)
            ->allowTransition(EngagementProcessIdentified::class, EngagementProcessBeingDescribed::class)
            ->allowTransition(EngagementProcessIdentified::class, EngagementProcessCancelled::class)
            ->allowTransition(EngagementProcessBeingDescribed::class, EngagementProcessReadyToAnalyse::class)
            ->allowTransition(EngagementProcessBeingDescribed::class, EngagementProcessCancelled::class)
            ->allowTransition(EngagementProcessReadyToAnalyse::class, EngagementProcessBeingAnalysed::class)
            ->allowTransition(EngagementProcessReadyToAnalyse::class, EngagementProcessCancelled::class)
            ->allowTransition(EngagementProcessBeingAnalysed::class, EngagementProcessAnalysed::class)
            ->allowTransition(EngagementProcessBeingAnalysed::class, EngagementProcessCancelled::class)
            ->allowTransition(EngagementProcessAnalysed::class, EngagementProcessAnalysedAndMapped::class)
            ->allowTransition(EngagementProcessAnalysed::class, EngagementProcessCancelled::class);
    }
}
