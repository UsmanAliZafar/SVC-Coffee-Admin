<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     */
    public $order;

    /**
     * Additional data for the email
     */
    public $additionalData;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, array $additionalData = [])
    {
        $this->order = $order;
        $this->additionalData = $additionalData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('app.name')
            ),
            subject: "Order Received - #{$this->order->order_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.order-created',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->getCustomerName(),
                'orderNumber' => $this->order->order_number,
                'orderDate' => $this->order->created_at->format('F d, Y'),
                'totalAmount' => $this->order->getFormattedTotal(),
                'items' => $this->order->items,
                'shippingAddress' => $this->order->getShippingAddress(),
                'trackingUrl' => $this->order->action_url ?? config('app.url') . '/track-order/' . $this->order->order_number,
                'paymentMethod' => ucfirst($this->order->payment_method),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
