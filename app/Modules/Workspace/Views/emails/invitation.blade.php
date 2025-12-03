<x-mail::message>
# You've Been Invited!

**{{ $inviter?->name ?? 'A team member' }}** has invited you to join **{{ $workspace->name }}** on {{ config('app.name') }}.

@if($personalMessage)
> {{ $personalMessage }}
@endif

## About {{ $workspace->name }}

Join {{ $inviter?->first_name ?? 'the team' }} and collaborate on projects, share updates, and get work done together.

<x-mail::button :url="$acceptUrl" color="primary">
Accept Invitation
</x-mail::button>

This invitation will expire in 7 days.

---

If you weren't expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
