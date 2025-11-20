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

                // ✅ FIX: Handle null cancelled_at
                'cancelledDate' => $this->order->cancelled_at
                    ? $this->order->cancelled_at->format('F d, Y h:i A')
                    : now()->format('F d, Y h:i A'),

                // ✅ FIX: Use reason from additionalData or order model
                'cancellationReason' => $this->additionalData['reason']
                    ?? $this->order->cancellation_reason
                    ?? 'Cancelled as per your request',

                'totalAmount' => $this->order->getFormattedTotal(),

                // ✅ Enhanced refund info
                'refundInfo' => $this->getRefundInfo(),

                'supportUrl' => config('app.url') . '/support',

                // ✅ Additional helpful info
                'orderDate' => $this->order->created_at->format('F d, Y'),
                'itemsCount' => $this->order->getTotalItemsCount(),
            ],
        );
    }

    /**
     * Get refund information based on payment status
     */
    private function getRefundInfo(): string
    {
        switch ($this->order->payment_status_key_code) {
            case 'PAYMENT_PAID':
                return 'Your refund of ' . $this->order->getFormattedTotal() . ' will be processed within 5-7 business days.';

            case 'PAYMENT_PARTIALLY_PAID':
                $paidAmount = $this->order->getPaymentSummary()['net_paid'] ?? 0;
                return 'Your refund of ' . $this->order->currency . ' ' . number_format($paidAmount, 2) . ' will be processed within 5-7 business days.';

            case 'PAYMENT_PENDING':
            case 'PAYMENT_FAILED':
                return 'No charges were made to your payment method.';

            case 'PAYMENT_REFUNDED':
            case 'PAYMENT_PARTIALLY_REFUNDED':
                return 'Refund has already been processed to your original payment method.';

            default:
                return 'Please contact support for refund details.';
        }
    }

    public function attachments(): array
    {
        return [];
    }
}
