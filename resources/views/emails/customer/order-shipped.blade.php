{{-- Path: resources/views/emails/customer/order-shipped.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon        = '🚚';
    $headerTitle       = 'Your Order Has Shipped!';
    $headerSubtitle    = 'On its way to you';
    $headerStyle       = 'green';
    $footerSupportText = "Questions? We're here to help!";
@endphp

@section('email_title', 'Order Shipped')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Exciting news! Your order has been shipped and is on its way.
        You can track your package using the information below.
    </p>

    {{-- Order Number Card --}}
    <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); border-radius: 8px; padding: 20px; margin-bottom: 30px; color: #ffffff; text-align: center;">
        <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Order Number</p>
        <h2 style="margin: 0; font-size: 28px; font-weight: 700;">#{{ $orderNumber }}</h2>
    </div>

    {{-- Tracking Information --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
            Tracking Information
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            @if($trackingNumber)
            <tr>
                <td style="padding: 10px 0; color: #666; font-size: 14px;">Tracking Number:</td>
                <td style="padding: 10px 0; text-align: right;">
                    <strong style="font-family: monospace; color: #333;">{{ $trackingNumber }}</strong>
                </td>
            </tr>
            @endif
            @if($carrier)
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 10px 0; color: #666; font-size: 14px;">Carrier:</td>
                <td style="padding: 10px 0; color: #333; text-align: right; font-weight: 600;">{{ $carrier }}</td>
            </tr>
            @endif
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 10px 0; color: #666; font-size: 14px;">Shipped Date:</td>
                <td style="padding: 10px 0; color: #333; text-align: right; font-weight: 600;">{{ $shippedDate }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 10px 0; color: #666; font-size: 14px;">Estimated Delivery:</td>
                <td style="padding: 10px 0; color: #5B914C; text-align: right; font-weight: 700;">{{ $estimatedDelivery }}</td>
            </tr>
        </table>
    </div>

    {{-- Track Package Button --}}
    @if($trackingUrl)
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $trackingUrl }}" style="display: inline-block; padding: 16px 40px; background-color: #5B914C; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 700; font-size: 16px;">
            Track Your Package →
        </a>
    </div>
    @endif

    {{-- Shipped Items --}}
    <div style="margin-top: 30px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #5B914C; padding-bottom: 10px;">
            Shipped Items
        </h3>
        @foreach($items as $item)
        <div style="padding: 12px 0; border-bottom: 1px solid #e9ecef;">
            <strong style="color: #333;">{{ $item->product_name }}</strong>
            <span style="color: #666; float: right;">Qty: {{ $item->quantity }}</span>
        </div>
        @endforeach
    </div>

@endsection
