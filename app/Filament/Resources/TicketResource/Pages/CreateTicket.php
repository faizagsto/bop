<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\BudgetType;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;


class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = Auth::id();
        $data['status'] = 'Waiting for approval : Branch Manager';
        $data['phone'] = Auth::user()->phone ?? '';

        return $data;
    }
    

    protected function handleRecordCreation(array $data): Model
    {
        $ticket = static::getModel()::create(Arr::except($data, ['budgetTotalsData', 'budgetEntriesData']));

        // Save budget totals
        $totalBudget = 0;
        foreach ($data['budgetTotalsData'] ?? [] as $budgetTypeId => $totalData) {
            $amount = $this->parseCurrency($totalData['budget_total'] ?? 0);
            $ticket->ticketBudgetTotals()->create([
                'budget_type_id' => $budgetTypeId,
                'budget_total' => $amount,
            ]);
            $totalBudget += $amount;
        }

        // Save budget entries
        foreach ($data['budgetEntriesData'] ?? [] as $budgetTypeId => $entries) {
            foreach ($entries as $entry) {
                $ticket->ticketBudgetEntries()->create([
                    'budget_type_id' => $budgetTypeId,
                    'coa_tag_id' => $entry['coa_tag_id'],
                    'budget' => $this->parseCurrency($entry['amount']),
                ]);
            }
        }

        // Now update the ticket's total_budget
        $ticket->total_budget = $totalBudget;
        $ticket->saveQuietly();

        return $ticket;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('previewAndSubmit')
                ->label('Buat Pengajuan')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pengajuan')
                ->modalSubmitActionLabel('Ajukan')
                ->modalCancelActionLabel('Kembali')
                ->modalWidth('4xl')
                ->modalFooterActionsAlignment('right')
                ->action(fn () => $this->submitTicket())
                ->modalContent(fn () => $this->renderPreviewModal()),
        ];
    }

    private function submitTicket(): void
    {
        try {
            $this->create();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Pengajuan tidak valid')
                ->body('Harap periksa form Anda dan perbaiki kesalahan yang ditandai')
                ->danger()
                ->persistent()
                ->send();

            throw $e;
        }
    }

    private function renderPreviewModal(): HtmlString
{
    $state = $this->form->getRawState();
    $formatCurrency = fn ($value) => 'Rp' . number_format((float) $this->parseCurrency($value), 0, ',', '.');

    // Calculate totals - only for budgets with values
    $totalProject = $this->parseCurrency($state['total_project'] ?? 0);
    $totalBudget = 0;
    $budgetTotals = [];
    
    foreach ($state['budgetTotalsData'] ?? [] as $budgetTypeId => $totalData) {
        $amount = $this->parseCurrency($totalData['budget_total'] ?? 0);
        if ($amount > 0) { // Only include budgets with amounts
            $budgetType = BudgetType::find($budgetTypeId);
            $budgetTotals[$budgetTypeId] = [
                'amount' => $amount,
                'name' => $budgetType ? $budgetType->name : "Budget Type $budgetTypeId"
            ];
            $totalBudget += $amount;
        }
    }

    $profitLoss = $totalProject - $totalBudget;
    $profitLossStyle = $profitLoss >= 0
        ? 'background-color: #dcfce7; color: #15803d; border: 1px solid #86efac;'
        : 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;';

    // Generate warnings (keeps all validation rules)
    $warningsHtml = $this->generateWarnings($state, $budgetTotals, $totalProject, $totalBudget);

    // Simplified budget display (no entries breakdown)
    $budgetBoxes = '';
    foreach ($budgetTotals as $budgetTypeId => $data) {
        $budgetBoxes .= <<<HTML
<div style="background-color: white; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #fde68a; margin-bottom: 0.5rem;">
    <div style="font-size: 0.875rem; color: #d97706;">{$data['name']}</div>
    <div style="font-size: 1.125rem; font-weight: 600;">{$formatCurrency($data['amount'])}</div>
</div>
HTML;
    }

    $content = <<<HTML
<div style="display: flex; flex-direction: column; gap: 1rem; color: #1f2937;">
    {$warningsHtml}
    
    <!-- Project Value -->
    <div style="background-color: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
        <div style="font-size: 0.875rem; margin-bottom: 0.25rem;">Total Nilai Proyek</div>
        <div style="font-size: 1.5rem; font-weight: 700;">
            {$formatCurrency($totalProject)}
        </div>
    </div>

    <!-- Budget Totals -->
    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem;">
        {$budgetBoxes}
    </div>

    <!-- Summary -->
    <div style="background-color: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span>Total Budget:</span>
            <span style="font-weight: 600;">{$formatCurrency($totalBudget)}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 0.75rem; border-radius: 0.5rem; {$profitLossStyle}">
            <span style="font-weight: 600;">Profit & Loss:</span>
            <span style="font-weight: 700;">{$formatCurrency($profitLoss)}</span>
        </div>
    </div>
</div>t
HTML;

    return new HtmlString($content);
}

private function generateWarnings(array $state, array $budgetTotals, float $totalProject, float $totalBudget): string
{
    $warnings = [];

    // Required fields check
    foreach (['project_id', 'title', 'content', 'total_project'] as $field) {
        if (empty($state[$field])) {
            $warnings[] = "Field " . str_replace('_', ' ', $field) . " harus diisi";
        }
    }

    // Budget validation (still checks entries vs totals but doesn't display details)
    foreach ($state['budgetEntriesData'] ?? [] as $budgetTypeId => $entries) {
        $budgetTotal = $budgetTotals[$budgetTypeId]['amount'] ?? 0;
        $entriesTotal = collect($entries)->sum(fn ($e) => $this->parseCurrency($e['amount'] ?? 0));
        
        if (abs($budgetTotal - $entriesTotal) > 0.01) {
            $budgetName = $budgetTotals[$budgetTypeId]['name'] ?? "Budget Type $budgetTypeId";
            $warnings[] = "Total detail untuk {$budgetName} tidak sesuai dengan budget";
        }
    }

    // Project vs Budget validation
    if ($totalProject <= $totalBudget) {
        $warnings[] = "Total Project ({$this->formatCurrency($totalProject)}) harus lebih besar dari Total Budget ({$this->formatCurrency($totalBudget)})";
    }

    if (empty($warnings)) return '';

    $items = implode('', array_map(fn ($w) => "<li style=\"font-size: 0.875rem; color: #b45309;\">{$w}</li>", $warnings));

    return <<<HTML
<div style="background-color: #fffbeb; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #f59e0b; margin-bottom: 1rem;">
    <div style="display: flex; align-items: flex-start;">
        <svg style="width: 1.25rem; height: 1.25rem; color: #d97706; margin-right: 0.75rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <div>
            <div style="font-size: 0.875rem; font-weight: 500; color: #92400e; margin-bottom: 0.5rem;">Perhatian:</div>
            <ul style="list-style-type: disc; padding-left: 1.25rem; margin: 0;">
                {$items}
            </ul>
        </div>
    </div>
</div>
HTML;
}

    private function formatCurrency(float $value): string
    {
        return 'Rp' . number_format($value, 0, ',', '.');
    }

    private function parseCurrency(string|int|null $value): float
    {
        return (float) str_replace(['.', ','], '', $value ?? 0);
    }
}