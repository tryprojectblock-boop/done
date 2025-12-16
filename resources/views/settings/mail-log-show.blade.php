@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('settings.index') }}" class="hover:text-primary">Settings</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('settings.mail-logs') }}" class="hover:text-primary">Mail Logs</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>View</span>
            </div>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-base-content">Mail Log Details</h1>
                <a href="{{ route('settings.mail-logs') }}" class="btn btn-ghost">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    Back to Logs
                </a>
            </div>
        </div>

        <!-- Email Info Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Email Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-base-content/60">Date:</span>
                        <span class="ml-2 font-medium">{{ $mailLog->created_at->format('M d, Y H:i:s') }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">Mailable Class:</span>
                        <span class="ml-2 font-mono text-xs">{{ $mailLog->mailable_class ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">From:</span>
                        <span class="ml-2 font-medium">
                            {{ $mailLog->from_name ? $mailLog->from_name . ' <' . $mailLog->from_address . '>' : $mailLog->from_address }}
                        </span>
                    </div>
                    <div>
                        <span class="text-base-content/60">To:</span>
                        <span class="ml-2 font-medium">{{ $mailLog->to_list }}</span>
                    </div>
                    @if($mailLog->cc)
                        <div>
                            <span class="text-base-content/60">CC:</span>
                            <span class="ml-2 font-medium">
                                {{ collect($mailLog->cc)->map(fn($item) => $item['address'] ?? $item)->implode(', ') }}
                            </span>
                        </div>
                    @endif
                    @if($mailLog->bcc)
                        <div>
                            <span class="text-base-content/60">BCC:</span>
                            <span class="ml-2 font-medium">
                                {{ collect($mailLog->bcc)->map(fn($item) => $item['address'] ?? $item)->implode(', ') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Subject Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">Subject</h2>
                <p class="font-medium">{{ $mailLog->subject }}</p>
            </div>
        </div>

        @php
            // Extract client ticket URL from email body
            $clientTicketUrl = null;
            $emailContent = $mailLog->html_body ?? $mailLog->text_body ?? '';
            if (preg_match('/href=["\']?(https?:\/\/[^"\'>\s]*\/ticket\/[^"\'>\s]+)["\']?/i', $emailContent, $matches)) {
                $clientTicketUrl = html_entity_decode($matches[1]);
            } elseif (preg_match('/(https?:\/\/[^\s<>]*\/ticket\/[^\s<>]+)/i', $emailContent, $matches)) {
                $clientTicketUrl = $matches[1];
            }
        @endphp

        @if($clientTicketUrl)
        <!-- Client Ticket Actions -->
        <div class="card bg-primary/10 border border-primary/20 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <span class="icon-[tabler--ticket] size-5 text-primary"></span>
                    Client Ticket View
                </h2>
                <p class="text-sm text-base-content/70 mb-4">
                    This email contains a link to the client ticket portal. Click below to view the ticket as the client would see it.
                </p>
                <div class="flex gap-2">
                    <a href="{{ $clientTicketUrl }}" target="_blank" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--external-link] size-4"></span>
                        View Ticket as Client
                    </a>
                    <button type="button" class="btn btn-ghost gap-2" onclick="copyToClipboard('{{ $clientTicketUrl }}')">
                        <span class="icon-[tabler--copy] size-4"></span>
                        Copy URL
                    </button>
                </div>
                <p class="text-xs text-base-content/50 mt-2 font-mono break-all">{{ $clientTicketUrl }}</p>
            </div>
        </div>
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('URL copied to clipboard!');
                });
            }
        </script>
        @endif

        @if($mailLog->attachments)
            <!-- Attachments Card -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-2">Attachments</h2>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($mailLog->attachments as $attachment)
                            <li>{{ $attachment['filename'] ?? 'Unknown' }} ({{ $attachment['content_type'] ?? '' }})</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- HTML Body Card -->
        @if($mailLog->html_body)
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">HTML Preview</h2>
                    <div class="border border-base-200 rounded-lg overflow-hidden">
                        <iframe
                            id="email-preview"
                            class="w-full min-h-[500px] bg-white"
                            sandbox="allow-same-origin"
                        ></iframe>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var iframe = document.getElementById('email-preview');
                    var doc = iframe.contentDocument || iframe.contentWindow.document;
                    doc.open();
                    doc.write({!! json_encode($mailLog->html_body) !!});
                    doc.close();
                });
            </script>
        @endif

        <!-- Text Body Card -->
        @if($mailLog->text_body)
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Plain Text</h2>
                    <pre class="text-sm bg-base-200 p-4 rounded-lg whitespace-pre-wrap overflow-x-auto">{{ $mailLog->text_body }}</pre>
                </div>
            </div>
        @endif

        <!-- Raw HTML Card (collapsible) -->
        @if($mailLog->html_body)
            <div class="collapse collapse-arrow bg-base-100 shadow">
                <input type="checkbox" />
                <div class="collapse-title text-lg font-medium">
                    Raw HTML Source
                </div>
                <div class="collapse-content">
                    <pre class="text-xs bg-base-200 p-4 rounded-lg whitespace-pre-wrap overflow-x-auto max-h-96">{{ $mailLog->html_body }}</pre>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
