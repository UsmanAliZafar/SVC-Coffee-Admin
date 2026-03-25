{{-- Path: resources/views/emails/customer/order-confirmed.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon     = '✓';
    $headerTitle    = 'Order Confirmed!';
    $headerSubtitle = 'Thank you for your order';
    $headerStyle    = 'green';
@endphp

@section('email_title', 'Order Confirmed')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Great news! Your order has been confirmed and we're getting it ready for shipment.
        You'll receive another email when your order ships with tracking information.
    </p>

    {{-- Order Summary Card --}}
    <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); border-radius: 8px; padding: 20px; margin-bottom: 30px; color: #ffffff; text-align: center;">
        <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Order Number</p>
        <h2 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: 1px;">#{{ $orderNumber }}</h2>
        <div style="height: 1px; background-color: rgba(255,255,255,0.3); margin: 15px 0;"></div>
        <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Order Total</p>
        <p style="margin: 0; font-size: 28px; font-weight: 700;">{{ $totalAmount }}</p>
    </div>

    {{-- Order Details --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
            Order Details
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Date:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $orderDate }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Status:</td>
                <td style="padding: 8px 0; text-align: right;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #5B914C; color: #ffffff; border-radius: 4px; font-size: 12px; font-weight: 600;">
                        CONFIRMED
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Order Items --}}
    <div style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
            Items in Your Order
        </h3>

        @foreach($items as $item)
        <div style="display: table; width: 100%; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e9ecef;">
            <div style="display: table-cell; vertical-align: top; width: 70%;">
                <p style="margin: 0 0 5px 0; font-weight: 600; color: #333; font-size: 15px;">{{ $item->product_name }}</p>
                @if($item->product_sku)
                <p style="margin: 0 0 5px 0; color: #999; font-size: 13px;">SKU: {{ $item->product_sku }}</p>
                @endif
                <p style="margin: 0; color: #666; font-size: 14px;">Quantity: {{ $item->quantity }}</p>
            </div>
            <div style="display: table-cell; vertical-align: top; text-align: right; width: 30%;">
                <p style="margin: 0; font-weight: 700; color: #5B914C; font-size: 16px;">
                    {{ store_currency_symbol() }} {{ number_format($item->total, 2) }}
                </p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Shipping Address --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #333;">
            📦 Shipping Address
        </h3>
        <p style="margin: 0; color: #666; line-height: 1.6; font-size: 14px;">
            {{ $shippingAddress }}
        </p>
    </div>

    {{-- What's Next --}}
    <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #004085;">
            📋 What Happens Next?
        </h3>
        <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #004085; line-height: 1.8;">
            <li>We're preparing your items for shipment</li>
            <li>You'll receive a tracking number once shipped</li>
            <li>Delivery typically takes 3-5 business days</li>
            <li>Track your order status anytime</li>
        </ul>
    </div>

@endsection
