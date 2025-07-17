<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectName extends Model
{
    
    protected $casts = [
    'from_date' => 'date',
    'to_date' => 'date',
    ];

    protected $fillable = [
        'name',
        'customer',
        'period',
        'pks_number',
        'from_date',
        'to_date',
    ];

    protected $table = 'project_names';

    public function getRouteKeyName()
    {
        return 'name';
    }
    // In ProjectName model:
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'project_name_id');
    }
}
