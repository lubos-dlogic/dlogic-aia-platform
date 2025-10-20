<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class UserActivityTimeline extends Widget
{
    protected static string $view = 'filament.resources.user-resource.widgets.user-activity-timeline';

    public ?Model $record = null;

    protected int | string | array $columnSpan = 'full';

    public function getActivities()
    {
        if (! $this->record) {
            return collect();
        }

        return $this->record
            ->activities()
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($activity) {
                return [
                    'description' => $activity->description,
                    'event' => $activity->event ?? 'updated',
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at,
                    'causer' => $activity->causer?->name ?? 'System',
                ];
            });
    }
}
