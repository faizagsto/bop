<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Ticket;

class TicketDone extends Notification
{
    use Queueable;

    public $ticket;
    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }


    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Selesai (Done)')
            ->greeting("Halo {$notifiable->name},")
            ->line("Ada pengajuan yang sudah selesai.")
            ->line("Pengajuan: **{$this->ticket->title}**")
            ->line("Nomor Pengajuan: **{$this->ticket->nomor_pengajuan}**")
            ->line("Status: **{$this->ticket->status}**")
            ->action('Lihat Pengajuan', url('admin/tickets/' . $this->ticket->id) . '/edit')
            ->line('Mohon segera diperiksa.')
            ->salutation('Salam Hormat,');
    }
}
