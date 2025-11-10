<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #5B914C 0%, #4a7a3d 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
        }
        .notification-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background-color: {{ $iconColor }};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        .notification-title {
            font-size: 20px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 15px;
            text-align: center;
        }
        .notification-message {
            font-size: 16px;
            color: #666666;
            margin-bottom: 25px;
            text-align: center;
            line-height: 1.8;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #5B914C;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333333;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666666;
        }
        .info-value {
            color: #333333;
        }
        .action-button {
            display: inline-block;
            padding: 14px 35px;
            background-color: #5B914C;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .action-button:hover {
            background-color: #4a7a3d;
        }
        .button-container {
            text-align: center;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 20px;
            text-align: center;
            font-size: 13px;
            color: #666666;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .email-footer a {
            color: #5B914C;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 25px 0;
        }
        .priority-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .priority-urgent {
            background-color: #dc3545;
            color: #ffffff;
        }
        .priority-high {
            background-color: #ffc107;
            color: #333333;
        }
        .priority-normal {
            background-color: #17a2b8;
            color: #ffffff;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            .email-body {
                padding: 25px 15px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label, .info-value {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ config('app.name') }}</h1>
            <p>Admin Notification System</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Priority Badge -->
            @if(in_array($notification->priority, ['urgent', 'high']))
            <div style="text-align: center; margin-bottom: 20px;">
                <span class="priority-badge priority-{{ $notification->priority }}">
                    {{ strtoupper($notification->priority) }} PRIORITY
                </span>
            </div>
            @endif

            <!-- Notification Icon -->
            @php
                $iconColor = match($notification->color) {
                    'success' => '#28a745',
                    'warning' => '#ffc107',
                    'danger' => '#dc3545',
                    'info' => '#17a2b8',
                    'primary' => '#007bff',
                    default => '#6c757d'
                };
            @endphp

            <!-- Title -->
            <h2 class="notification-title">{{ $notification->title }}</h2>

            <!-- Message -->
            <p class="notification-message">{{ $notification->message }}</p>

            <div class="divider"></div>

            <!-- Additional Data -->
            @if(!empty($data))
            <div class="info-box">
                <h3>Details</h3>
                @foreach($data as $key => $value)
                    @if(!in_array($key, ['action_url', 'store_url', 'store_name']))
                    <div class="info-row">
                        <span class="info-label">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                        <span class="info-value">{{ $value }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif

            <!-- Action Button -->
            @if($notification->action_url)
            <div class="button-container">
                <a href="{{ $notification->action_url }}" class="action-button">
                    View Details
                </a>
            </div>
            @endif
            <div class="divider"></div>

            <!-- Admin Info -->
            <div style="text-align: center; color: #999999; font-size: 14px;">
                <p>Hi {{ $admin->name }},</p>
                <p>This notification was sent to you based on your notification preferences.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>{{ config('app.url') }}</p>
            <p>
                <a href="{{ route('admin.notifications.settings') }}">Manage Notification Settings</a> |
                <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is an automated notification. Please do not reply to this email.
            </p>
            <p style="margin-top: 10px; font-size: 11px; color: #999999;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
