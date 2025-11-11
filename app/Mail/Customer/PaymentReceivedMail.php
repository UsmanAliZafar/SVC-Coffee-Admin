<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $additionalData;

    public function __construct(Order $order, array $additionalData = [])
    {
        $this->order = $order;
        $this->additionalData = $additionalData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('app.name')
            ),
            subject: "Payment Confirmed - Order #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.payment-received',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->getCustomerName(),
                'orderNumber' => $this->order->order_number,
                'paymentAmount' => $this->order->getFormattedTotal(),
                'paymentMethod' => ucfirst(str_replace('_', ' ', $this->order->payment_method)),
                'transactionId' => $this->order->transaction_id,
                'paidDate' => now()->format('F d, Y h:i A'),
                'orderStatus' => $this->order->getStatusLabel(),
                'items' => $this->order->items,
                // 'invoiceUrl' => //route('customer.orders.invoice', $this->order->id),
                'invoiceUrl' => config('app.url') . '/invoices/' . $this->order->order_number,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
