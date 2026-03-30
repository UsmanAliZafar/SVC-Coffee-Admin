<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Barryvdh\DomPDF\Facade\Pdf;

class SendInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public string $customSubject;
    public string $customMessage;

    public function __construct(Order $order, string $customSubject, string $customMessage)
    {
        $this->order = $order;
        $this->customSubject = $customSubject;
        $this->customMessage = $customMessage;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('app.name')
            ),
            subject: $this->customSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.send-invoice',
            with: [
                'order'         => $this->order,
                'customerName'  => $this->order->getCustomerName(),
                'orderNumber'   => $this->order->order_number,
                'orderDate'     => $this->order->created_at->format('F d, Y'),
                'totalAmount'   => $this->order->getFormattedTotal(),
                'items'         => $this->order->items,
                'customMessage' => $this->customMessage,
            ],
        );
    }

    public function attachments(): array
    {
        // Generate PDF invoice in memory and attach
        $pdf = Pdf::loadView('admin.orders.invoice-pdf', ['order' => $this->order]);

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'invoice-' . $this->order->order_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
