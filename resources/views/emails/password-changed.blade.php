<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
        }
        .icon-container {
            width: 60px;
            height: 60px;
            background-color: #dcfce7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon {
            width: 30px;
            height: 30px;
            color: #22c55e;
        }
        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #6b7280;
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .content p {
            margin: 0 0 10px;
        }
        .content p:last-child {
            margin-bottom: 0;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 25px;
        }
        .warning-box p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }
        .details {
            margin-bottom: 25px;
        }
        .details-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #6b7280;
            width: 120px;
            flex-shrink: 0;
        }
        .details-value {
            color: #1f2937;
            font-weight: 500;
        }
        .button {
            display: inline-block;
            background-color: #6366f1;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }
        .button:hover {
            background-color: #4f46e5;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #6366f1;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>

        <div class="icon-container">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>

        <h1>Password Changed Successfully</h1>
        <p class="subtitle">Your account password has been updated</p>

        <div class="content">
            <p>Hi {{ $user->first_name }},</p>
            <p>This email confirms that the password for your {{ config('app.name') }} account has been successfully changed.</p>
        </div>

        <div class="details">
            <div class="details-row">
                <span class="details-label">Account:</span>
                <span class="details-value">{{ $user->email }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Changed on:</span>
                <span class="details-value">{{ now()->format('F j, Y \a\t g:i A') }}</span>
            </div>
        </div>

        <div class="warning-box">
            <p><strong>Didn't make this change?</strong> If you did not change your password, please contact our support team immediately or reset your password to secure your account.</p>
        </div>

        <div class="button-container">
            <a href="{{ url('/login') }}" class="button">Sign In to Your Account</a>
        </div>

        <div class="footer">
            <p>This is an automated message from {{ config('app.name') }}.</p>
            <p>If you have any questions, please contact our <a href="mailto:support@newdone.com">support team</a>.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
