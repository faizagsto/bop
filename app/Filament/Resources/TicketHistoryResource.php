<?php

namespace App\Filament\Resources;

use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use App\Filament\Resources\TicketHistoryResource\Pages;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Tables\Enums\ActionsPosition;
use App\Filament\Resources\TicketResource\RelationManagers\CommentsRelationManager;

class TicketHistoryResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-s-clock';
    protected static ?string $navigationGroup = 'Pengajuan';
    protected static ?string $slug = 'ticket-history';

    public static function getLabel(): string
    {
        return 'Histori Pengajuan';
    }

    public static function getPluralLabel(): string
    {
        return 'Histori Pengajuan';
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Eager load ticketBudgetTotals, ticketBudgetEntries, and project.budgetTypes for admin role
            return Ticket::query()->with(['viewableUsers', 'ticketBudgetTotals', 'ticketBudgetEntries', 'project.budgetTypes']);
        }

        // Eager load ticketBudgetTotals, ticketBudgetEntries, and project.budgetTypes for other roles as well
        return Ticket::query()
            ->with(['viewableUsers', 'ticketBudgetTotals', 'ticketBudgetEntries', 'project.budgetTypes'])
            ->where(function ($query) use ($user) {
                $query
                    ->whereHas('viewableUsers', fn ($q) => $q->where('users.id', $user->id))
                    ->orWhere(function ($q) use ($user) {
                        $q->where('owner_id', $user->id)
                            ->where(function ($q) use ($user) {
                                $q->whereDoesntHave('viewableUsers', fn ($q) => $q->where('users.id', $user->id))
                                    ->orWhere(function ($q) use ($user) {
                                        $q->where('responsible_role', $user->role)
                                            ->where(function ($subQ) use ($user) {
                                                $subQ->where(function ($q) use ($user) {
                                                    $q->where('responsible_area', 'branch')
                                                        ->whereHas('owner', fn ($q) => $q->where('region_id', $user->region_id));
                                                })->orWhere(function ($q) use ($user) {
                                                    $q->where('responsible_area', 'main')
                                                        ->where('responsible_unit', $user->unit);
                                                });
                                            });
                                    });
                            });
                    });
            })
            ->where(function ($query) use ($user) {
                $query
                    ->whereNull('responsible_role')
                    ->orWhere('responsible_role', '!=', $user->role)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('responsible_area', 'branch')
                            ->whereHas('owner', fn ($q) => $q->where('region_id', '!=', $user->region_id));
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where('responsible_area', 'main')
                            ->where('responsible_unit', '!=', $user->unit);
                    });
            });
    }

    public static function applyEloquentFiltersToCollection(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return TicketResource::form($form);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengajuan')
                    ->label('Nomor Pengajuan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->label('Ticket')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->sortable()
                    ->label('Owner'),
                Tables\Columns\TextColumn::make('project.name')
                    ->sortable()
                    ->label('Project'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Waiting for approval : Branch Manager' => 'warning',
                        'Waiting for approval : reviewer operational' => 'warning',
                        'Waiting for approval : reviewer finance' => 'warning',
                        'Waiting for approval : reviewer sales' => 'warning',
                        'Waiting for approval : manager operational' => 'warning',
                        'Waiting for approval : manager finance' => 'warning',
                        'Waiting for approval : manager sales' => 'warning',
                        'Waiting for approval : cashier finance' => 'warning',
                        'Waiting for approval : bm' => 'warning',
                        'Revision : reviewer operational' => 'warning',
                        'Revision : reviewer finance' => 'warning',
                        'Revision : reviewer sales' => 'warning',
                        'Revision : manager operational' => 'warning',
                        'Revision : manager finance' => 'warning',
                        'Revision : manager sales' => 'warning',
                        'Revision : cashier finance' => 'warning',
                        'Revision : bm' => 'warning',
                        'Revision : requester' => 'warning',
                        'Done' => 'success',
                        'Closed' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_project')
                    ->label('Total Project')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('total_budget')
                    ->label('Total Budget')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->label('Tanggal Pengajuan'),
                Tables\Columns\TextColumn::make('expected_transfer_date')
                    ->label('Ekspektasi Tanggal Transfer')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('latestComment.transfer_date')
                    ->label('Tanggal Transfer')
                    ->date()
                    ->placeholder('Not set')
                    ->sortable()
                    ->tooltip('View comments for details'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->columns(2)
                    ->columnspan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('project_id')
                    ->form([
                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->label('Project')
                            ->preload()
                            ->placeholder('Select Project'),
                    ])                           
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['project_id'],
                                fn (Builder $query, $projectId): Builder => $query->where('project_id', $projectId),
                            );
                    })
                    ->label('Project')
                    ->columnSpan(1)
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                    Action::make('overview')
                        ->label('')
                        ->icon('heroicon-o-eye')
                        ->modalHeading('Overview')
                        ->modalContent(function (Ticket $record) {
                            $formatCurrency = fn ($value) => 'Rp' . number_format($value, 0, ',', '.');
                            
                            $totalProject = $record->total_project ?? 0;
                            $totalBudget = $record->ticketBudgetTotals->sum('budget_total') ?? 0;
                            $profitLoss = $totalProject - $totalBudget;

                            // Profit/Loss styling
                            $profitLossStyle = $profitLoss >= 0
                                ? 'background-color: #dcfce7; color: #15803d; border: 1px solid #86efac;'
                                : 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;';

                            // Build budget type boxes (without COA details)
                            $budgetBoxes = '';
                            foreach ($record->ticketBudgetTotals as $total) {
                                $budgetType = $total->budgetType;
                                $budgetBoxes .= <<<HTML
                    <div style="background-color: white; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #fde68a;">
                        <div style="font-size: 0.875rem; line-height: 1.25rem; font-weight: 500; color: #d97706;">{$budgetType->name}</div>
                        <div style="font-size: 1.125rem; line-height: 1.75rem; font-weight: 600;">{$formatCurrency($total->budget_total)}</div>
                        </div>
                    HTML;

                                $namaproject = $record->title ?? 'N/A';
                                $startDate = $record->projectName?->from_date?->format('d/m/Y') ?? '-';
                                $endDate = $record->projectName?->to_date?->format('d/m/Y') ?? '-';


                            }

                            $content = <<<HTML
                    <div style="display: flex; flex-direction: column; gap: 1rem; color: #1f2937;">
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <h4 style="font-size: 1.25rem; line-height: 1.75rem; font-weight: 600;">{$namaproject}</h4>
                        <p style="font-size: 0.875rem; line-height: 1.25rem; color: #4b5563;">Periode: {$startDate} - {$endDate}</p>
                    </div>


                    <div style="display: flex; flex-direction: column; gap: 1rem; color: #1f2937;">
                        <!-- Project Value -->
                        <div style="background-color: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                            <div style="font-size: 0.875rem; line-height: 1.25rem; font-weight: 500; margin-bottom: 0.25rem;">Total Nilai Proyek</div>
                            <div style="font-size: 1.5rem; line-height: 2rem; font-weight: 700;">
                                {$formatCurrency($totalProject)}
                            </div>
                        </div>

                        <!-- Budget Breakdown (2 columns) -->
                        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem;">
                            {$budgetBoxes}
                        </div>

                        <!-- Totals -->
                        <div style="background-color: #f9fafb; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 500;">Total Budget:</span>
                                <span style="font-weight: 700; font-size: 1rem">{$formatCurrency($totalBudget)}</span>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.125rem; line-height: 1.75rem; padding: 0.75rem; border-radius: 0.5rem; {$profitLossStyle}">
                                <span style="font-weight: 600;">Profit & Loss:</span>
                                <span style="font-weight: 700;">{$formatCurrency($profitLoss)}</span>
                            </div>
                        </div>
                    </div>
                    HTML;

                            // Return the content as an HtmlString

                    return new HtmlString($content);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->color('success')
                        ], position: ActionsPosition::BeforeColumns)
                        ->headerActions([
                Action::make('exportFiltered')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\CheckboxList::make('columns')
                            ->label('Kolom yang Diekspor')
                            ->options([
                                'nomor_pengajuan' => 'Nomor Pengajuan',
                                'owner.name' => 'Pemilik',
                                'owner.region.name' => 'BM',
                                'owner.email' => 'Email',
                                'title' => 'Judul',
                                'status' => 'Status',
                                'created_at' => 'Dibuat Pada',
                                'updated_at' => 'Diperbarui Pada',
                                'total_project' => 'Total Proyek',
                                'total_budget' => 'Total Anggaran',
                                'expected_transfer_date' => 'Tanggal Transfer yang Diharapkan',
                                'content' => 'Konten',
                                'latestComment.transfer_date' => 'Tanggal Transfer Aktual',
                            ])
                            ->required()
                            ->default([
                                'nomor_pengajuan',
                                'owner.name',
                                'owner.region.name',
                                'owner.email',
                                'title',
                                'status',
                                'created_at',
                                'updated_at',
                                'total_project',
                                'total_budget',
                                'expected_transfer_date',
                                'content',
                                'latestComment.transfer_date',
                            ])
                            ->columns(2),
                    ])
                    ->action(function (array $data, $livewire) {
                        $columns = $data['columns'];

                        // Retrieve filtered records
                        $records = $livewire->getFilteredSortedTableQuery()->get();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TicketExport($records, $columns),
                            'history-pengajuan.xlsx'
                        );
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketHistories::route('/'),
            'view' => Pages\ViewTicketHistory::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }
}