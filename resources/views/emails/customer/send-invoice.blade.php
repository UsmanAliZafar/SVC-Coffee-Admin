{{-- Path: resources/views/emails/customer/send-invoice.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon        = '🧾';
    $headerTitle       = 'Your Invoice';
    $headerSubtitle    = 'Order #' . $orderNumber;
    $headerStyle       = 'green';
    $footerSupportText  = 'Questions about your invoice or order?';
    $footerSupportColor = '#5B914C';
@endphp

@section('email_title', 'Invoice - #' . $orderNumber)

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    {{-- Custom message from admin --}}
    @if(!empty($customMessage))
    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        {{ $customMessage }}
    </p>
    @else
    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Please find your invoice for order <strong>#{{ $orderNumber }}</strong> attached to this email.
    </p>
    @endif

    {{-- Invoice Summary Box --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
            Invoice Summary
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Number:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Date:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $orderDate }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Total:</td>
                <td style="padding: 8px 0; color: #5B914C; text-align: right; font-weight: 700; font-size: 16px;">{{ $totalAmount }}</td>
            </tr>
        </table>
    </div>

    {{-- Order Items Table --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; overflow: hidden; margin-bottom: 25px;">
        <h3 style="margin: 0; font-size: 16px; color: #333; padding: 15px 20px; border-bottom: 1px solid #dee2e6;">
            Items Ordered
        </h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background-color: #e9ecef;">
                    <th style="padding: 10px 20px; text-align: left; color: #555; font-weight: 600;">Product</th>
                    <th style="padding: 10px 20px; text-align: center; color: #555; font-weight: 600;">Qty</th>
                    <th style="padding: 10px 20px; text-align: right; color: #555; font-weight: 600;">Unit Price</th>
                    <th style="padding: 10px 20px; text-align: right; color: #555; font-weight: 600;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr style="border-top: 1px solid #dee2e6;">
                    <td style="padding: 12px 20px; color: #333;">
                        {{ $item->product_name }}
                        @if($item->product_sku)
                        <br>
                        <span style="font-size: 12px; color: #999;">SKU: {{ $item->product_sku }}</span>
                        @endif
                    </td>
                    <td style="padding: 12px 20px; color: #333; text-align: center;">{{ $item->quantity }}</td>
                    <td style="padding: 12px 20px; color: #333; text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="padding: 12px 20px; color: #333; text-align: right; font-weight: 600;">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid #dee2e6; background-color: #e9ecef;">
                    <td colspan="3" style="padding: 12px 20px; text-align: right; font-weight: 700; color: #333; font-size: 15px;">
                        Grand Total:
                    </td>
                    <td style="padding: 12px 20px; text-align: right; font-weight: 700; color: #5B914C; font-size: 16px;">
                        {{ $totalAmount }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- PDF Attachment Notice --}}
    <div style="background-color: #d4edda; border-left: 4px solid #5B914C; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #155724; font-weight: 600;">📎 Invoice PDF Attached</p>
        <p style="margin: 10px 0 0 0; color: #155724; font-size: 14px;">
            A full PDF copy of your invoice (<strong>invoice-{{ $orderNumber }}.pdf</strong>) is attached to this email for your records.
        </p>
    </div>

    {{-- Closing --}}
    <p style="font-size: 15px; color: #666; line-height: 1.8; margin-bottom: 0;">
        Thank you for your business. We appreciate your trust in us!
    </p>

@endsection
