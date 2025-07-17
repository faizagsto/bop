<?php

namespace App\Filament\Resources\TicketBudgetEntryResource\Pages;

use App\Filament\Resources\TicketBudgetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketBudgetEntries extends ListRecords
{
    protected static string $resource = TicketBudgetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
