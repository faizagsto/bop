<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetType extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * The projects that belong to the budget type.
     */
    public function projects()
    { 
        return $this->belongsToMany(Project::class);
    }
    public function ticketBudgetEntries()
    {
        return $this->hasMany(TicketBudgetEntry::class);
    }
    public function ticketBudgetTotals()
    {
        return $this->hasMany(TicketBudgetTotal::class);
    }

    public function coaTag()
    {
        return $this->hasMany(COATag::class);
    }
    
}
