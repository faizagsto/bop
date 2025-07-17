<?php

namespace App\Filament\Resources\TicketHistoryResource\Pages;

use App\Filament\Resources\TicketHistoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTicketHistory extends ViewRecord
{
    protected static string $resource = TicketHistoryResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
{
    $record = $this->record;

    // Hydrate budgetTotalsData
    $data['budgetTotalsData'] = [];
    foreach ($record->ticketBudgetTotals as $total) {
        $data['budgetTotalsData'][$total->budget_type_id] = [
            'budget_total' => $total->budget_total,
        ];
    }

    // Hydrate budgetEntriesData
    $data['budgetEntriesData'] = [];
    foreach ($record->ticketBudgetEntries as $entry) {
        $data['budgetEntriesData'][$entry->budget_type_id][] = [
            'coa_tag_id' => $entry->coa_tag_id,
            'amount'     => $entry->budget,
        ];
    }

    // Optionally reset approval fields
    $data['approval_comment'] = '';
    $data['approval_attachment'] = null;

    return $data;
}
}
