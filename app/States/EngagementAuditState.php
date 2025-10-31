<?php

declare(strict_types=1);

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EngagementAuditState extends State
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(EngagementAuditDraft::class)
            ->allowTransition(EngagementAuditDraft::class, EngagementAuditScheduled::class)
            ->allowTransition(EngagementAuditDraft::class, EngagementAuditInProgress::class)
            ->allowTransition(EngagementAuditDraft::class, EngagementAuditCancelled::class)
            ->allowTransition(EngagementAuditScheduled::class, EngagementAuditInProgress::class)
            ->allowTransition(EngagementAuditScheduled::class, EngagementAuditCancelled::class)
            ->allowTransition(EngagementAuditInProgress::class, EngagementAuditCompleted::class)
            ->allowTransition(EngagementAuditInProgress::class, EngagementAuditCancelled::class)
            ->allowTransition(EngagementAuditCompleted::class, EngagementAuditInProgress::class)
            ->allowTransition(EngagementAuditCompleted::class, EngagementAuditCancelled::class);
    }
}
