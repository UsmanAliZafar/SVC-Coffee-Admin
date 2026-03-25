<?php

// Path: app/Http/Controllers/Admin/EmailPreviewController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailPreviewController extends Controller
{
    /**
     * List of all available email previews.
     * Each entry: 'route_key' => ['view' => '...', 'label' => '...']
     */
    private array $emails = [
        'order-received' => [
            'label' => 'Order Received',
            'view'  => 'emails.customer.order-received',
        ],
        'order-confirmed' => [
            'label' => 'Order Confirmed',
            'view'  => 'emails.customer.order-confirmed',
        ],
        'order-processing' => [
            'label' => 'Order Processing',
            'view'  => 'emails.customer.order-processing',
        ],
        'order-shipped' => [
            'label' => 'Order Shipped',
            'view'  => 'emails.customer.order-shipped',
        ],
        'order-delivered' => [
            'label' => 'Order Delivered',
            'view'  => 'emails.customer.order-delivered',
        ],
        'order-cancelled' => [
            'label' => 'Order Cancelled',
            'view'  => 'emails.customer.order-cancelled',
        ],
        'payment-confirmed' => [
            'label' => 'Payment Confirmed',
            'view'  => 'emails.customer.payment-received',
        ],
        'payment-failed' => [
            'label' => 'Payment Failed',
            'view'  => 'emails.customer.payment-failed',
        ],
        'refund-processed' => [
            'label' => 'Refund Processed',
            'view'  => 'emails.customer.order-refunded',
        ],
    ];

    // -------------------------------------------------------------------------
    // Index — show all email links in the browser
    // -------------------------------------------------------------------------
    public function index()
    {
        return view('emails.customer.preview.index', [
            'emails' => $this->emails,
        ]);
    }

    // -------------------------------------------------------------------------
    // Show — render a single email with dummy data
    // -------------------------------------------------------------------------
    public function show(string $email)
    {
        // 1. Key must exist in our list
        if (! array_key_exists($email, $this->emails)) {
            return response(
                "<h2>Email key <code>{$email}</code> not found.</h2>
                 <p>Available keys:</p><ul>"
                . implode('', array_map(
                    fn($k) => "<li><a href='/admin/email-preview/{$k}'>{$k}</a></li>",
                    array_keys($this->emails)
                ))
                . "</ul>",
                404
            );
        }

        $view = $this->emails[$email]['view'];

        // 2. Blade view file must exist on disk
        if (! view()->exists($view)) {
            return response(
                "<h2>View not found: <code>{$view}</code></h2>
                 <p>Expected file:</p>
                 <code>resources/views/" . str_replace('.', '/', $view) . ".blade.php</code>",
                404
            );
        }

        return view($view, $this->dummyData($email));
    }

    // -------------------------------------------------------------------------
    // Dummy data — realistic fake data for every template
    // -------------------------------------------------------------------------
    private function dummyData(string $email): array
    {
        // Shared across all emails
        $base = [
            'customerName'  => 'John Doe',
            'orderNumber'   => 'ORD-2025-00123',
            'orderDate'     => now()->format('d M Y'),
            'totalAmount'   => '$249.99',
            'trackingUrl'   => '#',
            'supportUrl'    => '#',
            'shippingAddress' => "123 Main Street\nApartment 4B\nNew York, NY 10001\nUnited States",
            'items'         => collect([
                (object)[
                    'product_name' => 'Breville Barista Express',
                    'product_sku'  => 'BRE-BES870XL',
                    'quantity'     => 1,
                    'total'        => 199.99,
                ],
                (object)[
                    'product_name' => 'Premium Arabica Coffee Beans (1kg)',
                    'product_sku'  => 'BEAN-ARB-1KG',
                    'quantity'     => 2,
                    'total'        => 50.00,
                ],
            ]),
        ];

        // Per-template extras
        $extras = match ($email) {

            'order-received' => [
                'paymentMethod' => 'Credit Card',
            ],

            'order-confirmed' => [],

            'order-processing' => [
                'processingDate'    => now()->format('d M Y, h:i A'),
                'estimatedShipDate' => now()->addDays(2)->format('d M Y'),
            ],

            'order-shipped' => [
                'trackingNumber'    => 'TRK-9876543210',
                'carrier'           => 'FedEx',
                'shippedDate'       => now()->format('d M Y'),
                'estimatedDelivery' => now()->addDays(3)->format('d M Y'),
            ],

            'order-delivered' => [
                'deliveredDate' => now()->format('d M Y'),
                'reviewUrl'     => '#',
            ],

            'order-cancelled' => [
                'cancellationReason' => 'Customer requested cancellation.',
                'cancelledDate'      => now()->format('d M Y'),
                'refundInfo'         => 'A full refund of $249.99 has been initiated and will appear in your account within 5–7 business days.',
            ],

            'payment-confirmed' => [
                'paymentAmount' => '$249.99',
                'paymentMethod' => 'Credit Card (Visa ****4242)',
                'transactionId' => 'TXN-20250325-ABC123',
                'paidDate'      => now()->format('d M Y, h:i A'),
                'orderStatus'   => 'Confirmed',
                'invoiceUrl'    => '#',
            ],

            'payment-failed' => [
                'paymentAmount' => '$249.99',
                'paymentMethod' => 'Credit Card (Visa ****4242)',
                'failureReason' => 'Your card was declined. Please check your card details or contact your bank.',
                'failureDate'   => now()->format('d M Y, h:i A'),
                'retryUrl'      => '#',
            ],

            'refund-processed' => [
                'refundAmount'   => '$249.99',
                'refundType'     => 'Full',
                'originalAmount' => '$249.99',
                'refundMethod'   => 'Original Payment Method',
                'refundedDate'   => now()->format('d M Y'),
                'processingTime' => '5–7 business days',
            ],

            default => [],
        };

        return array_merge($base, $extras);
    }
}
