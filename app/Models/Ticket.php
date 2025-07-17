<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketDone;
use App\Notifications\TicketClosed;


class Ticket extends Model
{

    protected $casts = [
        'owner_id' => 'integer',
        'total_budget' => 'float',
        'attachment_path' => 'array',
        'budget_total = float',
    ];
    
    protected $fillable = [
        'title',
        'content',
        'status',
        'priority',
        'phone',
        'expected_transfer_date',
        'attachment_path',

        // Budgeting
        'total_project',

        // Approval flow
        'responsible_role',
        'responsible_area',
        'responsible_unit',
        'current_step_order',

        // Foreign keys
        'project_id',
        'owner_id',
        'project_name_id',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }


    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticketBudgetEntries()
    {
        return $this->hasMany(TicketBudgetEntry::class);
    }

    public function ticketBudgetTotals()
    {
        return $this->hasMany(TicketBudgetTotal::class);
    }


    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function latestComment()
    {
        return $this->hasOne(Comment::class)->latestOfMany();
    }

   // In Ticket model:
    public function projectName() // This matches your relationship name in the select field
    {
        return $this->belongsTo(ProjectName::class, 'project_name_id')->withDefault([
            'name' => 'Pengajuan Baru' // Default if no project is selected
        ]);
    }


    public function approvalSteps(): Collection
    {
        return $this->project?->approvalSteps ?? collect();
    }

    public function sortedApprovalSteps(): Collection
    {
        return $this->approvalSteps()->sortBy('step_order')->values();
    }

    public function currentApprovalStep(): ?ApprovalStep
    {
        return $this->approvalSteps()
            ->where('step_order', $this->current_step_order)
            ->first();
    }

public function finalApprovalStep(): ?ApprovalStep
    {
        return $this->sortedApprovalSteps()->last();
    }

    public function moveToStep(ApprovalStep $step): void
    {
        $this->current_step_order = $step->step_order;
        $this->responsible_role = $step->role;
        $this->responsible_area = $step->area;
        $this->responsible_unit = $step->unit;
    }


    public function isUserResponsible(User $user): bool
    {
        $step = $this->currentApprovalStep();

        if (! $step) return false;

        $matchesRole = $user->role === $step->role;

        $matchesArea = match ($step->area) {
            'branch' => $user->region_id === $this->owner?->region_id, // Changed to region_id
            'main'   => $user->unit === $step->unit,
            default  => false
        };

        return $matchesRole && $matchesArea;
    }

       public function getResponsibleUsers(): Collection
    {
        return User::query()
            ->where('role', $this->responsible_role)
            ->when($this->responsible_area === 'branch', function ($query) {
                $query->where('region_id', $this->owner?->region_id); // Changed to region_id
            })
            ->when($this->responsible_area === 'main', function ($query) {
                $query->where('unit', $this->responsible_unit);
            })
            ->get();
    }


    public function scopeVisibleToUser($query, User $user)
    {
        // Bypass if admin
        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where(function ($query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    $query->where('responsible_role', $user->role)
                        ->where(function ($subQuery) use ($user) {
                            $subQuery->where(function ($q) use ($user) {
                                $q->where('responsible_area', 'branch')
                                    ->whereHas('owner', fn ($q) => $q->where('region_id', $user->region_id)); // Changed to region_id
                            })->orWhere(function ($q) use ($user) {
                                $q->where('responsible_area', 'main')
                                    ->where('responsible_unit', $user->unit);
                            });
                        });
                })
                ->orWhereHas('viewableUsers', fn ($q) => $q->where('users.id', $user->id));
        });
    }

     public function scopeWasResponsibleBefore($query, User $user)
    {
        if ($user->role === 'admin') {
            return $query;
        }

        return $query
            ->whereHas('viewableUsers', fn ($q) => $q->where('users.id', $user->id))
            ->where(function ($q) use ($user) {
                $q->where('responsible_role', '!=', $user->role)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('responsible_area', 'branch')
                            ->whereHas('owner', fn ($q) => $q->where('region_id', '!=', $user->region_id)); // Changed to region_id
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('responsible_area', 'main')
                            ->where('responsible_unit', '!=', $user->unit);
                    });
            });
    }
    
        

    public function viewableUsers()
    {
        return $this->belongsToMany(User::class, 'ticket_user_views')->withTimestamps();
    }

    public function formatApprovalStatus($step): string
    {
        if ($this->responsible_area === 'main') {
            return 'Waiting for approval : ' . $step->role . ' ' . $this->responsible_unit;
        }
    
        if ($this->responsible_area === 'branch') {
            $region = $this->owner?->region ?? 'Unknown';
            return 'Waiting for approval : ' . $step->role;
        }
    
        return 'Waiting for approval : ' . $step->role;
    }

    public function formatRevisionStatus($step): string
    {
        if ($this->responsible_area === 'main') {
            return 'Revision : ' . $step->role . ' ' . $this->responsible_unit;
        }
    
        if ($this->responsible_area === 'branch') {
            $region = $this->owner?->region ?? 'Unknown';
            return 'Revision : ' . $step->role;
        }
    
        return 'Revision : ' . $step->role;
    }

    //Notification jalan
    public function notifyResponsibleUsers(): void
    {
        $users = $this->getResponsibleUsers();
        if ($users->isEmpty()) return;

        $users->each->notify(new TicketAssigned($this));
    }
    // Notification if Done
    public function notifyTicketDone(): void
    {
        $branchManagers = $this->viewableUsers()
            ->where('role', 'bm') // Assuming 'bm' is your branch manager role
            ->get();
        
        if ($branchManagers->isNotEmpty()) {
            $branchManagers->each->notify(new TicketDone($this));
        }
    }

    // Notification if Closed
    public function notifyTicketClosed(): void
    {
        $branchManagers = $this->viewableUsers()
            ->where('role', 'bm') // Assuming 'bm' is your branch manager role
            ->get();
        
        if ($branchManagers->isNotEmpty()) {
            $branchManagers->each->notify(new TicketClosed($this));
        }
    }


    
    
        public function approve(User $user)
    {
        $steps = $this->sortedApprovalSteps();
        $currentIndex = $steps->search(fn($step) => $step->step_order === $this->current_step_order);
        $nextStep = $steps->get($currentIndex + 1);

        // If there's a next step, move to it and update status accordingly
        if ($nextStep) {
            $this->moveToStep($nextStep);
            $this->status = $this->formatApprovalStatus($nextStep);
            $this->notifyResponsibleUsers(); 
        } else {
            // If there's no next step, mark as approved (last step)
            $this->status = 'Done';
            
            $lastStep = $steps->last(); // Get the last approval step
            if ($lastStep) {
                // Only clear if the step is approved (i.e., someone has completed this step)
                if ($this->current_step_order === $lastStep->step_order) {
                    // Clear responsibility for the last person responsible in the approval flow
                    $this->responsible_role = null;
                    $this->responsible_area = null;
                    $this->responsible_unit = null;
                }
            }
            $this->notifyTicketDone(); // Notify that the ticket is done
        }

        // Sync the current user as a viewable user
        $this->viewableUsers()->syncWithoutDetaching($user->id);
        
        // Now, gather all the users who have handled the ticket, including the requester (owner)
        $handledUsers = collect(); // Initialize an empty collection
        
        // Loop through all approval steps to gather users who handled the ticket
        foreach ($steps as $step) {
            if ($step->users) {
                $handledUsers = $handledUsers->merge($step->users->pluck('id'));
            }
        }

        // Add the owner (requester) to the list of viewable users
        $handledUsers->push($this->owner_id); // Assuming `owner_id` stores the requester's ID

        // Ensure there are no duplicates and update viewable users
        $this->viewableUsers()->syncWithoutDetaching($handledUsers->unique()->toArray());


        // Save the ticket after all updates
        $this->save();
    }


    public function reject(User $user)
    {
        $steps = $this->sortedApprovalSteps();
        $currentIndex = $steps->search(fn($step) => $step->step_order === $this->current_step_order);

        // If rejecting from main office (skip back to BM)
        if ($this->responsible_area === 'main') {
            // Find the last BM (branch) step before current position
            $bmStep = $steps->take($currentIndex)
                            ->reverse()
                            ->firstWhere('area', 'branch');
            
            if ($bmStep) {
                $this->moveToStep($bmStep);
                $this->status = $this->formatRevisionStatus($bmStep);
                $this->notifyResponsibleUsers();
            } else {
                // If no BM found and we're closing the ticket
                $this->status = 'Closed';
                $this->responsible_role = null;
                $this->responsible_area = null;
                $this->responsible_unit = null;
                $this->notifyTicketClosed(); // Notify that the ticket is closed
            }
        }
        // If rejecting from BM (branch) - go back 1 step to requester
        elseif ($this->responsible_area === 'branch') {
            $prevStep = $steps->get($currentIndex - 1);
            
            if ($prevStep) {
                $this->moveToStep($prevStep);
                $this->status = $this->formatRevisionStatus($prevStep);
            } else {
                // If no previous step and we're closing the ticket
                $this->status = 'Closed';
                $this->responsible_role = null;
                $this->responsible_area = null;
                $this->responsible_unit = null;
            }
        }
        // For any other case (shouldn't normally happen)
        else {
            $prevStep = $steps->get($currentIndex - 1);
            
            if ($prevStep) {
                $this->moveToStep($prevStep);
                $this->status = $this->formatRevisionStatus($prevStep);
            } else {
                $this->status = 'Closed';
                $this->responsible_role = null;
                $this->responsible_area = null;
                $this->responsible_unit = null;
            }
        }
        
        // Always sync the rejecting user as viewable
        $this->viewableUsers()->syncWithoutDetaching($user->id);
        
        // If closing, ensure owner can view it
        if ($this->status === 'Closed') {
            $this->viewableUsers()->syncWithoutDetaching($this->owner_id);
        }

        $this->notifyResponsibleUsers();  

        $this->save();
    }

    public function close(User $user)
    {
        $this->status = 'Closed';
        $this->responsible_role = null;
        $this->responsible_area = null;
        $this->responsible_unit = null;
        $this->viewableUsers()->syncWithoutDetaching($user->id);
        $this->notifyTicketClosed(); // Notify that the ticket is closed 
        $this->save();
    }

  protected static function booted(): void
{
    static::creating(function (Ticket $ticket) {
        // Set approval step
        $steps = $ticket->project?->approvalSteps;
        $firstStep = $steps
            ?->where('role', '!=', 'requester')
            ->sortBy('step_order')
            ->first();

        if ($firstStep) {
            $ticket->moveToStep($firstStep);
        }

        // Generate nomor pengajuan
        $latestId = Ticket::max('id') + 1;
        $month = now()->format('m');
        $year = now()->format('y');
        $ticket->nomor_pengajuan = 'BOP/' . $month . '/' . $year . '/' . str_pad($latestId, 4, '0', STR_PAD_LEFT);

        if ($ticket->owner_id) {
            $owner = \App\Models\User::find($ticket->owner_id);
            if ($owner) {
                $ticket->phone = $owner->phone;
            }
        }

        // Set title based on project name if available
        if ($ticket->project_name_id) {
            $project = \App\Models\ProjectName::find($ticket->project_name_id);
            if ($project) {
                $ticket->title = "{$project->name} - {$project->customer} ({$project->period}) - PKS: {$project->pks_number}";
            }
        }

        // Initialize total_budget to 0 (will be updated after creation)
        $ticket->total_budget = 0;
    });

    // Update total budget after creation when budget totals are saved
    static::created(function (Ticket $ticket) {
        $ticket->notifyResponsibleUsers();
        
    });

    // Update total budget when saving existing ticket
    static::saving(function (Ticket $ticket) {
        if ($ticket->exists) {
            if (!$ticket->relationLoaded('ticketBudgetTotals')) {
                $ticket->load('ticketBudgetTotals');
            }
            $ticket->total_budget = $ticket->ticketBudgetTotals->sum('budget_total');
        }
    });
}


}