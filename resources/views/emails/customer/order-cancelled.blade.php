<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: #fff; padding: 40px 20px; text-align: center;">
            <div style="font-size: 50px; margin-bottom: 10px;">❌</div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Order Cancelled</h1>
        </div>

        <div style="padding: 40px 30px;">
            <p style="font-size: 18px; margin-bottom: 20px;">Hi <strong>{{ $customerName }}</strong>,</p>

            <p style="font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 25px;">
                Your order #{{ $orderNumber }} has been cancelled as requested.
            </p>

            <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #721c24;"><strong>Cancellation Reason:</strong></p>
                <p style="margin: 10px 0 0 0; color: #721c24;">{{ $cancellationReason }}</p>
            </div>

            <div style="background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #333; border-bottom: 2px solid #dc3545; padding-bottom: 10px;">Order Details</h3>
                <p style="margin: 0; color: #666;"><strong>Order Number:</strong> #{{ $orderNumber }}</p>
                <p style="margin: 10px 0; color: #666;"><strong>Order Amount:</strong> {{ $totalAmount }}</p>
                <p style="margin: 10px 0 0 0; color: #666;"><strong>Cancelled Date:</strong> {{ $cancelledDate }}</p>
            </div>

            <div style="background-color: #d1ecf1; border-left: 4px solid: #0c5460; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
                <p style="margin: 0; color: #0c5460; font-weight: 600;">💰 Refund Information</p>
                <p style="margin: 10px 0 0 0; color: #0c5460; font-size: 14px;">{{ $refundInfo }}</p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Have questions about the cancellation?</p>
                <p style="margin: 0;">
                    <a href="{{ $supportUrl }}" style="color: #dc3545; text-decoration: none; font-weight: 600;">Contact Support</a>
                </p>
            </div>
        </div>

        <div style="background-color: #f8f9fa; padding: 30px 20px; text-align: center; border-top: 1px solid #e9ecef;">
            <p style="margin: 0 0 10px 0; font-weight: 600; color: #333;">{{ config('app.name') }}</p>
            <p style="margin: 0; font-size: 12px; color: #999;">© {{ date('Y') }} {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
