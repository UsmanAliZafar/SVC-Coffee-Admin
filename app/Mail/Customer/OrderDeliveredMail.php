<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class OrderDeliveredMail extends Mailable
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
            subject: "Your Order Has Been Delivered! - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.order-delivered',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->getCustomerName(),
                'orderNumber' => $this->order->order_number,
                'deliveredDate' => $this->order->delivered_at->format('F d, Y h:i A'),
                'totalAmount' => $this->order->getFormattedTotal(),
                'items' => $this->order->items,
                'reviewUrl' => route('customer.orders.review', $this->order->id),
                'supportUrl' => config('app.url') . '/support',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
