<?php

namespace App\Filament\Resources\TicketHistoryResource\Pages;

use App\Filament\Resources\TicketHistoryResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;


class ListTicketHistories extends ListRecords
{
    protected static string $resource = TicketHistoryResource::class;
    // protected static ?string $title = 'Histori Pengajuan';

    public function getTabs(): array
{
    $baseQuery = TicketHistoryResource::getEloquentQuery();
    
    return [
        'All' => Tab::make()
            ->badge($baseQuery->count()),
            
        'In Progress' => Tab::make()
            ->badge($baseQuery->clone()->whereNotIn('status', ['Done', 'Closed', 'Approved', 'Rejected', 'Waiting for approval : cashier finance'])->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', ['Done', 'Closed', 'Approved', 'Rejected', 'Waiting for approval : cashier finance'])),
            
        'Cashier' => Tab::make()
            ->badge($baseQuery->clone()->where('status', 'Waiting for approval : cashier finance')->count())
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Waiting for approval : cashier finance')),
            
        'Done' => Tab::make()
            ->badge($baseQuery->clone()->where('status', 'Done')->count())
            ->badgeColor('success')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Done')),
            
        'Closed' => Tab::make()
            ->badge($baseQuery->clone()->where('status', 'Closed')->count())
            ->badgeColor('danger')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Closed')),
    ];
}
    
}
