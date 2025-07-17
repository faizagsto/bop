<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        // Admins can do anything
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_ticket::history');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->can('view_ticket::history');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_ticket::history');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can('update_ticket::history');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('delete_ticket::history');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_ticket::history');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->can('force_delete_ticket::history');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_ticket::history');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->can('restore_ticket::history');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_ticket::history');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Ticket $ticket): bool
    {
        return $user->can('replicate_ticket::history');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_ticket::history');
    }

    public function approve(User $user, Ticket $ticket): bool
    {
        return $ticket->isUserResponsible($user);
    }

    public function reject(User $user, Ticket $ticket): bool
    {
        // if ($ticket->project_id === 2) { // BOP rules
        //     if ($user->unit === 'finance') return false;
        //     if ($ticket->responsible_role === 'requester' || $ticket->owner_id === $user->id) return false;
        // }

        return $ticket->isUserResponsible($user);
    }

    public function close(User $user, Ticket $ticket): bool
    {
        if ($ticket->project_id === 2) {
            if ($user->unit === 'sales') return false;
            return $ticket->responsible_area === 'branch';
        }

        return $ticket->isUserResponsible($user);
    }

}
