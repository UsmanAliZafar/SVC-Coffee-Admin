{{-- Path: resources/views/emails/customer/order-delivered.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon         = '🎉';
    $headerTitle        = 'Delivered Successfully!';
    $headerSubtitle     = 'Your order has arrived';
    $headerStyle        = 'green';
    $footerSupportText  = 'Need help with your order?';
    $footerSupportColor = '#28a745';
@endphp

@section('email_title', 'Order Delivered')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Great news! Your order <strong>#{{ $orderNumber }}</strong> has been delivered. We hope you love your purchase!
    </p>

    {{-- Delivered confirmation banner --}}
    <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #155724; font-weight: 600;">
            ✓ Delivered on {{ $deliveredDate }}
        </p>
    </div>

    {{-- Order Summary --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
            Order Summary
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Number:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Total:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $totalAmount }}</td>
            </tr>
        </table>
    </div>

@endsection
