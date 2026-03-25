{{-- Path: resources/views/emails/customers/order-cancelled.blade.php --}}

@extends('emails.customers.layouts.email-master')

@php
    $headerIcon         = '❌';
    $headerTitle        = 'Order Cancelled';
    $headerSubtitle     = '';
    $headerStyle        = 'red';
    $footerSupportText  = 'Have questions about the cancellation?';
    $footerSupportColor = '#dc3545';
@endphp

@section('email_title', 'Order Cancelled')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Your order <strong>#{{ $orderNumber }}</strong> has been cancelled as requested.
    </p>

    {{-- Cancellation Reason --}}
    <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #721c24; font-weight: 600;">Cancellation Reason:</p>
        <p style="margin: 10px 0 0 0; color: #721c24; font-size: 14px;">{{ $cancellationReason }}</p>
    </div>

    {{-- Order Details --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">
            Order Details
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Number:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Amount:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $totalAmount }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Cancelled Date:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $cancelledDate }}</td>
            </tr>
        </table>
    </div>

    {{-- Refund Information --}}
    <div style="background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #0c5460; font-weight: 600;">💰 Refund Information</p>
        <p style="margin: 10px 0 0 0; color: #0c5460; font-size: 14px;">{{ $refundInfo }}</p>
    </div>

@endsection
