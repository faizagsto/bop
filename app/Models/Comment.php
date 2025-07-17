<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{

    protected $casts = [
    'transfer_date' => 'date',
    ];
    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'attachment_path',
        'action',
        'transfer_date'
    ];
    
    public function ticket(): BelongsTo
{
    return $this->belongsTo(Ticket::class);
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
