<?php

declare(strict_types=1);

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ClientState extends State
{
    abstract public function actionText(): string;

    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(ClientDraft::class)
            ->allowTransition(ClientDraft::class, ClientActive::class)
            ->allowTransition(ClientActive::class, ClientInactive::class)
            ->allowTransition(ClientInactive::class, ClientActive::class)
            ->allowTransition(ClientInactive::class, ClientDraft::class)
            ->allowTransition(ClientActive::class, ClientArchived::class)
            ->allowTransition(ClientActive::class, ClientDraft::class)
            ->allowTransition(ClientInactive::class, ClientArchived::class)
            ->allowTransition(ClientArchived::class, ClientActive::class)
            ->allowTransition(ClientArchived::class, ClientInactive::class);
    }
}
