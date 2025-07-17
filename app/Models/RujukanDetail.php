<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RujukanDetail extends Model
{

    protected $fillable = ['ticket_id', 'coa_tag_id', 'description', 'amount'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function coaTag()
    {
        return $this->belongsTo(COATag::class);
    }
}
