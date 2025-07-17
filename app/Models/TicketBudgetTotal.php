<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketBudgetTotal extends Model
{
    protected $fillable = [
        'ticket_id',
        'budget_type_id',
        'budget_total',
        'coa_tag_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function budgetType()
    {
        return $this->belongsTo(BudgetType::class);
    }
    public function coaTag()
    {
        return $this->belongsTo(COATag::class);
    }
}
