<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return response()->json(Ticket::all());
    }

    public function active()
    {
        return response()->json(
            Ticket::whereNotIn('status', ['Done', 'Closed'])
                ->get());
    }

    public function history()
    {
        return response()->json(
            Ticket::whereIn('status', ['Done', 'Closed'])
                ->get());
    }
}
