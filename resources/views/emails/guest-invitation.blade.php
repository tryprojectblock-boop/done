<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited</title>
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
            margin-bottom: 25px;
        }
        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .type-client { background-color: #d1fae5; color: #065f46; }
        .type-external_consultant { background-color: #dbeafe; color: #1e40af; }
        .button {
            display: inline-block;
            background-color: #6366f1;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
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
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 0 6px 6px 0;
            margin: 25px 0;
        }
        .info-box p {
            margin: 0;
            color: #1e40af;
            font-size: 14px;
        }
        .expiry-note {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 0 6px 6px 0;
            margin: 25px 0;
        }
        .expiry-note p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
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

        <h1>You're Invited!</h1>
        <p class="subtitle">{{ $invitedBy->full_name }} has invited you to join as a guest</p>

        <div class="content">
            <p>Hi {{ $guest->first_name }},</p>
            <p>You've been invited to join <strong>{{ $invitedBy->company->name ?? config('app.name') }}</strong> as a guest.</p>
            <p>You will be able to view and interact with projects and tasks that are shared with you.</p>
            <p>Click the button below to complete your profile and set up your account:</p>
        </div>

        <div class="button-container">
            <a href="{{ $signupUrl }}" class="button">Complete Your Profile</a>
        </div>

        <div class="info-box">
            <p><strong>What you'll need to set up:</strong></p>
            <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #1e40af; font-size: 14px;">
                <li>Create a secure password</li>
                <li>Add your last name</li>
                <li>Select your timezone</li>
                <li>Add a brief description (optional)</li>
            </ul>
        </div>

        <div class="expiry-note">
            <p><strong>Note:</strong> This invitation link will expire in 7 days. If it expires, please contact the team to request a new invitation.</p>
        </div>

        <div class="footer">
            <p>This invitation was sent by {{ $invitedBy->full_name }} ({{ $invitedBy->email }})</p>
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
