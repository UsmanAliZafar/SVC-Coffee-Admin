{{-- Path: resources/views/emails/customer/payment-failed.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon         = '⚠️';
    $headerTitle        = 'Payment Failed';
    $headerSubtitle     = 'Action Required';
    $headerStyle        = 'red';
    $footerSupportText  = 'Need help with payment?';
    $footerSupportColor = '#dc3545';
@endphp

@section('email_title', 'Payment Failed')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        We were unable to process your payment for order <strong>#{{ $orderNumber }}</strong>.
        Please review the details below and try again.
    </p>

    {{-- Payment Error Banner --}}
    <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #721c24; font-weight: 600;">Payment Error</p>
        <p style="margin: 10px 0 0 0; color: #721c24; font-size: 14px;">{{ $failureReason }}</p>
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
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Payment Amount:</td>
                <td style="padding: 8px 0; color: #dc3545; text-align: right; font-weight: 700; font-size: 18px;">{{ $paymentAmount }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Payment Method:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $paymentMethod }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Failed Date:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $failureDate }}</td>
            </tr>
        </table>
    </div>

    {{-- Common Reasons --}}
    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #856404; font-weight: 600;">Common Reasons for Payment Failure:</p>
        <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404; font-size: 14px; line-height: 1.8;">
            <li>Insufficient funds</li>
            <li>Incorrect card details</li>
            <li>Card expired</li>
            <li>Bank declined the transaction</li>
        </ul>
    </div>

@endsection
