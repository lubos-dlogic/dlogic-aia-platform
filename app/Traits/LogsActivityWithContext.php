<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;

/**
 * Provides helper methods for logging activities with custom context.
 *
 * This trait works alongside the LogsActivity trait from spatie/laravel-activitylog
 * to enable logging changes made by automation, system processes, or with custom properties.
 */
trait LogsActivityWithContext
{
    /**
     * Log an activity performed by an automation/process.
     *
     * @param string $description Description of the activity (e.g., "updated", "created")
     * @param string $processName Name of the automation/process
     * @param array<string, mixed>|null $additionalData Optional additional data to log
     */
    public function logActivityByProcess(string $description, string $processName, ?array $additionalData = null): Activity
    {
        $properties = [
            'source' => 'process',
            'process_name' => $processName,
        ];

        if ($additionalData !== null) {
            $properties['data'] = $additionalData;
        }

        return activity()
            ->performedOn($this)
            ->causedByAnonymous()
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Log an activity performed by the system.
     *
     * @param string $description Description of the activity (e.g., "status changed", "expired")
     * @param array<string, mixed>|null $data Optional data to log
     */
    public function logActivityBySystem(string $description, ?array $data = null): Activity
    {
        $properties = [
            'source' => 'system',
        ];

        if ($data !== null) {
            $properties['data'] = $data;
        }

        return activity()
            ->performedOn($this)
            ->causedByAnonymous()
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Log an activity with custom properties.
     *
     * This is a flexible method for logging activities with any custom context.
     *
     * @param string $description Description of the activity
     * @param array<string, mixed> $properties Custom properties to log
     * @param Model|null $causer Optional user or model that caused the activity
     */
    public function logActivityWithProperties(string $description, array $properties, ?Model $causer = null): Activity
    {
        $activityLogger = activity()
            ->performedOn($this)
            ->withProperties($properties);

        if ($causer !== null) {
            $activityLogger->causedBy($causer);
        } else {
            $activityLogger->causedByAnonymous();
        }

        return $activityLogger->log($description);
    }

    /**
     * Log an activity performed by a user with additional context data.
     *
     * Useful when you need to log user actions with extra metadata like
     * API request data, form submissions, or webhook payloads.
     *
     * @param string $description Description of the activity
     * @param Model $user The user who performed the activity
     * @param array<string, mixed>|null $data Optional additional data to log
     */
    public function logActivityByUser(string $description, Model $user, ?array $data = null): Activity
    {
        $properties = [
            'source' => 'user',
        ];

        if ($data !== null) {
            $properties['data'] = $data;
        }

        return activity()
            ->performedOn($this)
            ->causedBy($user)
            ->withProperties($properties)
            ->log($description);
    }
}
