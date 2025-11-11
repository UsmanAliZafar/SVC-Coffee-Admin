<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class PaymentFailedMail extends Mailable
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
            subject: "Payment Failed - Action Required for Order #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.payment-failed',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->getCustomerName(),
                'orderNumber' => $this->order->order_number,
                'paymentAmount' => $this->order->getFormattedTotal(),
                'paymentMethod' => ucfirst(str_replace('_', ' ', $this->order->payment_method)),
                'failureReason' => $this->additionalData['failure_reason'] ?? 'Payment could not be processed',
                'failureDate' => now()->format('F d, Y h:i A'),
                'retryUrl' => route('customer.orders.payment', $this->order->id),
                'supportUrl' => route('customer.support'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
