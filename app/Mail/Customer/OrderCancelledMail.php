<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class OrderCancelledMail extends Mailable
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
            subject: "Order Cancelled - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.order-cancelled',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->getCustomerName(),
                'orderNumber' => $this->order->order_number,
                'cancelledDate' => $this->order->cancelled_at->format('F d, Y h:i A'),
                'cancellationReason' => $this->order->cancellation_reason ?? 'Cancelled as per your request',
                'totalAmount' => $this->order->getFormattedTotal(),
                'refundInfo' => $this->order->payment_status_key_code === 'PAYMENT_PAID'
                    ? 'Your refund will be processed within 5-7 business days.'
                    : 'No charges were made to your payment method.',
                'supportUrl' => config('app.url') . '/support',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
