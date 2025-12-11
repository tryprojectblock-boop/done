<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
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
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-owner { background-color: #fee2e2; color: #991b1b; }
        .role-admin { background-color: #fef3c7; color: #92400e; }
        .role-member { background-color: #dbeafe; color: #1e40af; }
        .role-guest { background-color: #f3f4f6; color: #374151; }
        .button {
            display: inline-block;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 5px;
        }
        .button-primary {
            background-color: #10b981;
            color: #ffffff !important;
        }
        .button-primary:hover {
            background-color: #059669;
        }
        .button-secondary {
            background-color: #f3f4f6;
            color: #374151 !important;
            border: 1px solid #d1d5db;
        }
        .button-secondary:hover {
            background-color: #e5e7eb;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .company-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .company-info {
            color: #6b7280;
            font-size: 14px;
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

        <h1>Team Invitation</h1>
        <p class="subtitle">You've been invited to join a new team!</p>

        <div class="content">
            <p>Hi {{ $invitation->user->first_name }},</p>
            <p><strong>{{ $invitedBy->full_name }}</strong> has invited you to join their team as a <span class="role-badge role-{{ $invitation->role }}">{{ \App\Models\User::ROLES[$invitation->role]['label'] ?? ucfirst($invitation->role) }}</span>.</p>
        </div>

        <div class="company-box">
            <div class="company-name">{{ $invitation->company->name ?? 'New Team' }}</div>
            <div class="company-info">You'll have access to workspaces, tasks, and more</div>
        </div>

        <div class="info-box">
            <p><strong>What happens when you accept:</strong></p>
            <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #1e40af; font-size: 14px;">
                <li>You'll join this team with your existing account</li>
                <li>Access workspaces, tasks, and projects shared with you</li>
                <li>Collaborate with team members</li>
                <li>Your current teams remain unaffected</li>
            </ul>
        </div>

        <div class="button-container">
            <a href="{{ $invitationUrl }}" class="button button-primary">View Invitation</a>
        </div>

        <div class="expiry-note">
            <p><strong>Note:</strong> This invitation will expire in 7 days. If it expires, please contact {{ $invitedBy->first_name }} to request a new invitation.</p>
        </div>

        <div class="footer">
            <p>This invitation was sent by {{ $invitedBy->full_name }} ({{ $invitedBy->email }})</p>
            <p>If you didn't expect this invitation, you can safely ignore this email or decline it.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
