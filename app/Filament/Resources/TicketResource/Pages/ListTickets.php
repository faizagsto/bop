<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

use App\Models\Ticket;


class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
             ->label('Buat Pengajuan'),
        ];
    }
    // protected static ?string $title = 'Pengajuan';

    // protected function getTableQuery(): Builder
    // {
    //     return Ticket::visibleToUser(auth()->user());
    // }
    
}
