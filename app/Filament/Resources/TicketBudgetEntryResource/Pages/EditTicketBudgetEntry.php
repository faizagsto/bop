<?php

namespace App\Filament\Resources\TicketBudgetEntryResource\Pages;

use App\Filament\Resources\TicketBudgetEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicketBudgetEntry extends EditRecord
{
    protected static string $resource = TicketBudgetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
