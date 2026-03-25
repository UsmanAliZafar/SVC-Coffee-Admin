{{-- Path: resources/views/emails/customer/refund-processed.blade.php --}}

@extends('emails.customer.layouts.email-master')

@php
    $headerIcon         = '💰';
    $headerTitle        = 'Refund Processed';
    $headerSubtitle     = '';
    $headerStyle        = 'teal';
    $footerSupportText  = 'Questions about your refund?';
    $footerSupportColor = '#17a2b8';
@endphp

@section('email_title', 'Refund Processed')

@section('email_content')

    {{-- Greeting --}}
    <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
        Hi <strong>{{ $customerName }}</strong>,
    </p>

    <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
        Your refund for order <strong>#{{ $orderNumber }}</strong> has been successfully processed.
    </p>

    {{-- Refund Amount Card --}}
    <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 8px; padding: 25px; margin-bottom: 30px; color: #ffffff; text-align: center;">
        <p style="margin: 0 0 5px 0; font-size: 14px; opacity: 0.9;">Refund Amount</p>
        <h2 style="margin: 0; font-size: 36px; font-weight: 700;">{{ $refundAmount }}</h2>
        <p style="margin: 10px 0 0 0; font-size: 13px; opacity: 0.9; text-transform: uppercase;">{{ $refundType }} REFUND</p>
    </div>

    {{-- Refund Details --}}
    <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
            Refund Details
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Order Number:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">#{{ $orderNumber }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Original Amount:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $originalAmount }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Refund Method:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $refundMethod }}</td>
            </tr>
            <tr style="border-top: 1px solid #dee2e6;">
                <td style="padding: 8px 0; color: #666; font-size: 14px;">Processed Date:</td>
                <td style="padding: 8px 0; color: #333; text-align: right; font-weight: 600;">{{ $refundedDate }}</td>
            </tr>
        </table>
    </div>

    {{-- Processing Time notice --}}
    <div style="background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <p style="margin: 0; color: #004085; font-weight: 600;">⏰ Processing Time</p>
        <p style="margin: 10px 0 0 0; color: #004085; font-size: 14px;">
            The refund will appear in your account within {{ $processingTime }}.
        </p>
    </div>

@endsection
