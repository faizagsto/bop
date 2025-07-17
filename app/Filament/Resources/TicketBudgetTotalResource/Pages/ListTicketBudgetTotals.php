<?php

namespace App\Filament\Resources\TicketBudgetTotalResource\Pages;

use App\Filament\Resources\TicketBudgetTotalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTicketBudgetTotals extends ListRecords
{
    protected static string $resource = TicketBudgetTotalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
