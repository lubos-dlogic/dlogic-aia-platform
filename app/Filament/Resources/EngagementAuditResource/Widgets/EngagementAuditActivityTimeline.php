<?php

declare(strict_types=1);

namespace App\Filament\Resources\EngagementAuditResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class EngagementAuditActivityTimeline extends Widget
{
    protected static string $view = 'filament.resources.engagement-audit-resource.widgets.engagement-audit-activity-timeline';

    public ?Model $record = null;

    protected int | string | array $columnSpan = 'full';

    #[On('refreshActivityTimeline')]
    public function refreshTimeline(): void
    {
        // This method will be called when the event is dispatched
        // Livewire will automatically re-render the component
    }

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
