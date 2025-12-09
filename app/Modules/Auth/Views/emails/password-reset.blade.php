<x-mail::message>
# Reset Your Password

Hi {{ $user->first_name }},

We received a request to reset your password for your {{ config('app.name') }} account.

Click the button below to reset your password:

<x-mail::button :url="$resetUrl">
Reset Password
</x-mail::button>

This link will expire in **60 minutes**.

If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.

Thanks,<br>
{{ config('app.name') }} Team

<x-mail::subcopy>
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: {{ $resetUrl }}
</x-mail::subcopy>
</x-mail::message>
