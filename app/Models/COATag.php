<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class COATag extends Model
{
    protected $table = 'coa_tags';
    
    protected $fillable = ['name','budget_type_id'];

    public function bopDetails()
    {
        return $this->hasMany(BOPDetail::class);
    }

    public function budgetType()
    {
    return $this->belongsTo(BudgetType::class);
    }
}