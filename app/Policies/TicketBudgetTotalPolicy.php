<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TicketBudgetTotal;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketBudgetTotalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ticket::budget::total');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketBudgetTotal $ticketBudgetTotal): bool
    {
        return $user->can('view_ticket::budget::total');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_ticket::budget::total');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketBudgetTotal $ticketBudgetTotal): bool
    {
        return $user->can('update_ticket::budget::total');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketBudgetTotal $ticketBudgetTotal): bool
    {
        return $user->can('delete_ticket::budget::total');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_ticket::budget::total');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, TicketBudgetTotal $ticketBudgetTotal): bool
    {
        return $user->can('force_delete_ticket::budget::total');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_ticket::budget::total');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, TicketBudgetTotal $ticketBudgetTotal): bool
    {
        return $user->can('restore_ticket::budget::total');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_ticket::budget::total');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, TicketBudgetTotal $ticketBudgetTotal): bool
    {
        return $user->can('replicate_ticket::budget::total');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_ticket::budget::total');
    }
}
