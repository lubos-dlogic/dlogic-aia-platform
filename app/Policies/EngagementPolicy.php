<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Engagement;
use App\Models\User;

class EngagementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_engagement');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Engagement $engagement): bool
    {
        return $user->can('view_engagement');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_engagement');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Engagement $engagement): bool
    {
        return $user->can('update_engagement');
    }

    /**
     * Determine whether the user can change the state of the model.
     *
     * This is a custom permission separate from regular update,
     * allowing granular control over who can change engagement states/statuses.
     */
    public function changeState(User $user, Engagement $engagement): bool
    {
        return $user->can('change_state_engagement');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Engagement $engagement): bool
    {
        return $user->can('delete_engagement');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_engagement');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Engagement $engagement): bool
    {
        return $user->can('restore_engagement');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_engagement');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Engagement $engagement): bool
    {
        return $user->can('force_delete_engagement');
    }

    /**
     * Determine whether the user can bulk force delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_engagement');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Engagement $engagement): bool
    {
        return $user->can('replicate_engagement');
    }

    /**
     * Determine whether the user can reorder models.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_engagement');
    }
}
