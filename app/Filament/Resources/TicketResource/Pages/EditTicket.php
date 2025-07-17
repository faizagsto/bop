<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;

use Illuminate\Support\Facades\Gate;



class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

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
    
    protected function getFormActions(): array
    {
        $user = Auth::user();
        $ticket = $this->record;

        return array_filter([

            // ✅ Approve button
            Gate::allows('approve', $ticket) ? Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-m-check')
                ->color('success')
                ->modalHeading('Approve Ticket')
                ->form([
                    Textarea::make('comment')->label('Optional Comment')->required()->rows(3),
                    FileUpload::make('attachment')->label('Optional Attachment')
                        ->disk('public')->directory('attachments')->preserveFilenames(),
                    DatePicker::make('transfer_date')
                        ->label('Transfer Date')
                        ->required()
                        ->default(now()->addDays(3))
                        ->minDate(now())
                        ->maxDate(now()->addDays(30))
                        ->helperText('Set the date for the transfer of funds.')
                        ->visible(fn () => in_array(auth()->user()->role, ['cashier', 'admin'])), 
                ])
                ->action(function (array $data, $record) use ($user) {

                    $this->callHook('beforeSave');
                    $this->record->update($this->form->getState());
                    $this->callHook('afterSave');

                    $this->record->comments()->create([
                        'action' => 'Approve',
                        'user_id' => $user->id,
                        'body' => $data['comment'],
                        'attachment_path' => $data['attachment'] ?? null,
                        'transfer_date' => $data['transfer_date'] ?? null,
                    ]);

                    $this->record->approve($user);

                    return redirect(TicketResource::getUrl());
                }) : null,

            Gate::allows('reject', $ticket) ? Action::make('reject')
                ->label('Revision')
                ->icon('heroicon-m-x-mark')
                ->color('warning')
                ->modalHeading('Reject Ticket')
                ->form([
                    Textarea::make('comment')->label('Reason for Revision')->required(),
                    FileUpload::make('attachment')->label('Optional Attachment')
                        ->disk('public')->directory('attachments')->visibility('public')->preserveFilenames(),
                ])
                ->action(function (array $data, $record) use ($user) {
                    $record->comments()->create([
                        'action' => 'Revision',
                        'user_id' => $user->id,
                        'body' => $data['comment'],
                        'attachment_path' => $data['attachment'] ?? null,
                    ]);
                    $record->reject($user);
                    return redirect(TicketResource::getUrl());
                }) : null,

            Gate::allows('close', $ticket) ? Action::make('close')
                ->label('Close')
                ->icon('heroicon-m-x-mark')
                ->color('danger')
                ->modalHeading('Close Ticket')
                ->form([
                    Textarea::make('comment')->label('Reason for Closing')->required(),
                    FileUpload::make('attachment')->label('Optional Attachment')
                        ->disk('public')->directory('attachments')->visibility('public')->preserveFilenames(),
                ])
                ->action(function (array $data, $record) use ($user) {
                    $record->comments()->create([
                        'action' => 'Close',
                        'user_id' => $user->id,
                        'body' => $data['comment'],
                        'attachment_path' => $data['attachment'] ?? null,
                    ]);
                    $record->close($user);
                    return redirect(TicketResource::getUrl());
                }) : null,

        ]);
    }

protected function handleRecordCreation(array $data): Model
{
    $ticket = static::getModel()::create(Arr::except($data, ['budgetTotalsData', 'budgetEntriesData']));

    // Save totals
    foreach ($data['budgetTotalsData'] ?? [] as $budgetTypeId => $totalData) {
        $ticket->ticketBudgetTotals()->create([
            'budget_type_id' => $budgetTypeId,
            'budget_total'   => str_replace(['.', ','], '', $totalData['budget_total'] ?? 0),
        ]);
    }

    // Save entries
    foreach ($data['budgetEntriesData'] ?? [] as $budgetTypeId => $entries) {
        foreach ($entries as $entry) {
            $ticket->ticketBudgetEntries()->create([
                'budget_type_id' => $budgetTypeId,
                'coa_tag_id'     => $entry['coa_tag_id'],
                'budget'         => str_replace(['.', ','], '', $entry['amount']),
            ]);
        }
    }

    return $ticket;
}
    
    

}
