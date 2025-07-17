<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StatsDashboard extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();
        $isMaster = $user->hasRole('admin'); // Adjust role name if needed
        $isRequester = fn ($ticket) => $ticket->owner_id === $user->id;

        // Get all tickets visible to the user
        $visibleTickets = Ticket::visibleToUser($user)->get();

        return [
            // 🕚 In-progress tickets user can view but not act on
            Stat::make(
                '',
                $visibleTickets->filter(fn($ticket) =>
                    !$ticket->isUserResponsible($user) &&
                    (
                        $ticket->viewableUsers->contains($user) ||
                        $isRequester($ticket) ||
                        $isMaster
                    ) &&
                    !in_array(Str::lower($ticket->status), [
                        'done',
                        'closed',
                        'waiting for approval : cashier finance',
                    ])
                )->count()
            )
            ->description('🕚 Pengajuan yang sedang diproses')
            ->color('gray')
            ->url(route('filament.admin.resources.ticket-history.index', [
                'activeTab' => 'In Progress',
            ])),

            // 📄 Tickets waiting for cashier approval
            Stat::make(
                '',
                $visibleTickets->filter(fn($ticket) =>
                    Str::lower($ticket->status) === 'waiting for approval : cashier finance' &&
                    (
                        $ticket->viewableUsers->contains($user) ||
                        $isRequester($ticket) ||
                        $isMaster
                    )
                )->count()
            )
            ->description('📄 Pengajuan yang menunggu persetujuan kasir')
            ->color('warning')
            ->url(route('filament.admin.resources.ticket-history.index', [
                'activeTab' => 'Cashier',
            ])),

            // ✅ Done tickets user can view
            Stat::make(
                '',
                $visibleTickets->filter(fn($ticket) =>
                    Str::lower($ticket->status) === 'done' &&
                    (
                        $ticket->viewableUsers->contains($user) ||
                        $isRequester($ticket) ||
                        $isMaster
                    )
                )->count()
            )
            ->description('✅ Pengajuan yang sudah selesai')
            ->color('success')
            ->url(route('filament.admin.resources.ticket-history.index', [
                'activeTab' => 'Done',
            ])),

            // ❌ Closed tickets user can view
            Stat::make(
                '',
                $visibleTickets->filter(fn($ticket) =>
                    Str::lower($ticket->status) === 'closed' &&
                    (
                        $ticket->viewableUsers->contains($user) ||
                        $isRequester($ticket) ||
                        $isMaster
                    )
                )->count()
            )
            ->description('❌ Pengajuan yang sudah ditutup')
            ->color('danger')
            ->url(route('filament.admin.resources.ticket-history.index', [
                'activeTab' => 'Closed',
            ])),

            // ⚠️ Tickets that need user action
            Stat::make(
                '',
                $visibleTickets->filter(fn($ticket) =>
                    $ticket->isUserResponsible($user) &&
                    !in_array(Str::lower($ticket->status), ['done', 'closed'])
                )->count()
            )
            ->description('⚠️ Pengajuan yang perlu ditindak')
            ->color('warning')
            ->url(route('filament.admin.resources.tickets.index')),
        ];
    }
}
