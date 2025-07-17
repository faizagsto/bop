<?php

namespace App\Filament\Resources\TicketBudgetEntryResource\Pages;

use App\Filament\Resources\TicketBudgetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketBudgetEntry extends CreateRecord
{
    protected static string $resource = TicketBudgetEntryResource::class;
}
