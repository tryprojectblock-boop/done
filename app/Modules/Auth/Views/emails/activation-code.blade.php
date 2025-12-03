<x-mail::message>
# Verify Your Email Address

You're almost there! Use the activation code below to verify your email address and continue setting up your account.

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; padding: 20px;">
{{ $code }}
</div>
</x-mail::panel>

This code will expire in **72 hours** ({{ $expiresAt->format('F j, Y \a\t g:i A') }}).

If you didn't create an account with {{ config('app.name') }}, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
