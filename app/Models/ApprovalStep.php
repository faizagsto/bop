<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'step_order',
        'role',
        'area',
        'unit',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function name(): string
        {
            return "{$this->step_order}. {$this->role} ({$this->area})";
        }
    

}
