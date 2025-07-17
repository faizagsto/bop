<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketBudgetEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'budget_type_id',
        'coa_tag_id',
        'budget',
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
