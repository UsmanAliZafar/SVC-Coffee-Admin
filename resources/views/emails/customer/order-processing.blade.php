{{-- Path: resources/views/emails/customer/order-processing.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon     = '⚙️';
    $headerTitle    = "We're Preparing Your Order!";
    $headerSubtitle = '';
    $headerStyle    = 'blue';
@endphp

@section('email_title', 'Order Processing')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Good news! Your order <strong>#{{ $orderNumber }}</strong> is now being processed.
        We're carefully preparing your items for shipment.
    </p>

    {{-- Order Number Card --}}
    <div style="background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%); border-radius: 8px; padding: 20px; margin-bottom: 30px; color: #ffffff; text-align: center;">
        <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Order Number</p>
        <h2 style="margin: 0; font-size: 28px; font-weight: 700;">#{{ $orderNumber }}</h2>
    </div>

    {{-- Order Status --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
            Order Status
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Current Status:</td>
                <td style="padding: 8px 0; text-align: right;">
                    <span style="display: inline-block; padding: 4px 12px; background-color: #007bff; color: #ffffff; border-radius: 4px; font-size: 12px; font-weight: 600;">
                        PROCESSING
                    </span>
                </td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Processing Started:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $processingDate }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Est. Ship Date:</td>
                <td style="padding: 8px 0; color: #5B914C; text-align: right; font-weight: 700;">{{ $estimatedShipDate }}</td>
            </tr>
        </table>
    </div>

    {{-- What's Happening Now --}}
    <div style="background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #0c5460; font-weight: 600;">What's Happening Now?</p>
        <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #0c5460; line-height: 1.8;">
            <li>Quality checking your items</li>
            <li>Preparing packaging materials</li>
            <li>Scheduling pickup with carrier</li>
        </ul>
    </div>
@endsection
