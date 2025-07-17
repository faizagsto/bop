<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketHistoryResource;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;

class ViewTicket extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\TicketResource::class;

}


