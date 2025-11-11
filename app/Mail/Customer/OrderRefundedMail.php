<?php

namespace App\Mail\Customer;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class OrderRefundedMail extends Mailable
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
            subject: "Refund Processed - #{$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.order-refunded',
            with: [
                'order' => $this->order,
                'customerName' => $this->order->getCustomerName(),
                'orderNumber' => $this->order->order_number,
                'refundAmount' => $this->additionalData['refund_amount']
                    ?? $this->order->currency . ' ' . number_format($this->order->refunded_amount, 2),
                'refundType' => $this->additionalData['refund_type'] ?? 'full',
                'refundedDate' => $this->order->refunded_at->format('F d, Y h:i A'),
                'refundMethod' => $this->order->payment_method,
                'processingTime' => $this->getRefundProcessingTime($this->order->payment_method),
                'originalAmount' => $this->order->getFormattedTotal(),
            ],
        );
    }

    private function getRefundProcessingTime(string $paymentMethod): string
    {
        return match(strtolower($paymentMethod)) {
            'credit_card', 'debit_card' => '5-10 business days',
            'paypal' => '1-3 business days',
            'stripe' => '5-7 business days',
            default => '5-10 business days',
        };
    }

    public function attachments(): array
    {
        return [];
    }
}
