<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Invoice $invoice,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $invoice = $this->invoice->loadMissing('invoiceItems');

        return [
            'message' => "You have a new invoice.",
            'link' => "/invoices?id={$this->invoice->id}",
        ];
    }
}
