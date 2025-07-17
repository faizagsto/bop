<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }
    public function budgetTypes()
    {
        return $this->belongsToMany(BudgetType::class);
    }




}
