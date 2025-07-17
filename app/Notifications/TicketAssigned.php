<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue; // Optional if you want to queue it
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Ticket;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan baru yang perlu ditindaklanjuti')
            ->greeting("Halo {$notifiable->name},")
            ->line("Ada pengajuan baru yang anda perlu tindaklanjuti.")
            ->line("Pengajuan: **{$this->ticket->title}**")
            ->line("Nomor Pengajuan: **{$this->ticket->nomor_pengajuan}**")
            ->line("Status: **{$this->ticket->status}**")
            ->action('Lihat Pengajuan', url('admin/tickets/' . $this->ticket->id) . '/edit')
            ->line('Mohon segera diperiksa.')
            ->salutation('Salam Hormat,');
    }

}
// namespace App\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Notifications\Messages\MailMessage;
// use Illuminate\Notifications\Notification;

// class TicketAssigned extends Notification
// {
//     use Queueable;

//     /**
//      * Create a new notification instance.
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Get the notification's delivery channels.
//      *
//      * @return array<int, string>
//      */
//     public function via(object $notifiable): array
//     {
//         return ['mail'];
//     }

//     /**
//      * Get the mail representation of the notification.
//      */
//     public function toMail(object $notifiable): MailMessage
//     {
//         return (new MailMessage)
//                     ->line('The introduction to the notification.')
//                     ->action('Notification Action', url('/'))
//                     ->line('Thank you for using our application!');
//     }

//     /**
//      * Get the array representation of the notification.
//      *
//      * @return array<string, mixed>
//      */
//     public function toArray(object $notifiable): array
//     {
//         return [
//             //
//         ];
//     }
// }


