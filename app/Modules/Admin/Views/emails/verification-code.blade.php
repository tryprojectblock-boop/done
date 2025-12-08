<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #6366f1;">
        <h1 style="color: #6366f1; margin: 0;">Admin Panel</h1>
        <p style="color: #666; margin: 5px 0 0;">{{ config('app.name') }}</p>
    </div>

    <div style="padding: 30px 0;">
        <h2 style="color: #333;">Hello {{ $adminUser->name }},</h2>

        <p>You requested a verification code to access the Admin Panel. Use the code below to continue:</p>

        <div style="text-align: center; padding: 30px 0;">
            <div style="display: inline-block; background: #f3f4f6; padding: 20px 40px; border-radius: 8px; letter-spacing: 8px; font-size: 32px; font-weight: bold; color: #6366f1;">
                {{ $code }}
            </div>
        </div>

        <p style="color: #666; font-size: 14px;">This code will expire in <strong>10 minutes</strong>.</p>

        <p style="color: #666; font-size: 14px;">If you didn't request this code, please ignore this email or contact support if you have concerns.</p>
    </div>

    <div style="border-top: 1px solid #eee; padding-top: 20px; text-align: center; color: #999; font-size: 12px;">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated message. Please do not reply.</p>
    </div>
</body>
</html>
