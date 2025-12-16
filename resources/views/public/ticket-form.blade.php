<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $form->name }} - {{ $workspace->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: {{ $form->background_color ?? '#f3f4f6' }};
        }
        .btn-primary {
            background-color: {{ $form->primary_color ?? '#6366f1' }};
        }
        .btn-primary:hover {
            filter: brightness(0.9);
        }
        .focus-primary:focus {
            border-color: {{ $form->primary_color ?? '#6366f1' }};
            box-shadow: 0 0 0 3px {{ $form->primary_color ?? '#6366f1' }}33;
        }
        .text-primary {
            color: {{ $form->primary_color ?? '#6366f1' }};
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4">
    <div class="max-w-xl mx-auto">
        <!-- Logo -->
        @if($form->logo_url)
        <div class="text-center mb-6">
            <img src="{{ $form->logo_url }}" alt="{{ $workspace->name }}" class="h-12 mx-auto">
        </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100">
                <h1 class="text-2xl font-bold text-gray-900">{{ $form->name }}</h1>
                @if($form->description)
                <p class="text-gray-600 mt-1">{{ $form->description }}</p>
                @endif
            </div>

            <!-- Form -->
            <form id="ticket-form" class="p-6 space-y-5">
                @csrf

                <!-- Honeypot field (hidden from users) -->
                @if($form->enable_honeypot)
                <div style="position: absolute; left: -9999px;" aria-hidden="true">
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                </div>
                @endif

                @if($form->show_name)
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Your Name
                        @if($form->name_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="text" id="name" name="name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="John Doe" {{ $form->name_required ? 'required' : '' }}>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="name"></p>
                </div>
                @endif

                @if($form->show_email)
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address
                        @if($form->email_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="john@example.com" {{ $form->email_required ? 'required' : '' }}>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="email"></p>
                </div>
                @endif

                @if($form->show_phone)
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Phone Number
                        @if($form->phone_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="tel" id="phone" name="phone" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="+1 (555) 123-4567" {{ $form->phone_required ? 'required' : '' }}>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="phone"></p>
                </div>
                @endif

                @if($form->show_subject)
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                        Subject
                        @if($form->subject_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="text" id="subject" name="subject" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="Brief description of your issue" {{ $form->subject_required ? 'required' : '' }}>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="subject"></p>
                </div>
                @endif

                @if($form->show_department && $departments->count() > 0)
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Department
                        @if($form->department_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <select id="department_id" name="department_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition bg-white" {{ $form->department_required ? 'required' : '' }}>
                        <option value="">Select a department</option>
                        @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="department_id"></p>
                </div>
                @endif

                @if($form->show_priority && $priorities->count() > 0)
                <div>
                    <label for="priority_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Priority
                        @if($form->priority_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <select id="priority_id" name="priority_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition bg-white" {{ $form->priority_required ? 'required' : '' }}>
                        <option value="">Select a priority</option>
                        @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="priority_id"></p>
                </div>
                @endif

                @if($form->show_description)
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                        @if($form->description_required)<span class="text-red-500">*</span>@endif
                    </label>
                    <textarea id="description" name="description" rows="5" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition resize-none" placeholder="Please describe your issue in detail..." {{ $form->description_required ? 'required' : '' }}></textarea>
                    <p class="text-red-500 text-sm mt-1 hidden" data-error="description"></p>
                </div>
                @endif

                @if($form->show_attachments)
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">
                        Attachments
                    </label>
                    <input type="file" id="attachments" name="attachments[]" multiple class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    <p class="text-gray-500 text-xs mt-1">You can attach multiple files (max 10MB each)</p>
                </div>
                @endif

                {{-- Custom Fields --}}
                @if(isset($customFields) && $customFields->count() > 0)
                    @foreach($customFields as $field)
                    <div>
                        <label for="custom_{{ $field->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $field->label }}
                            @if($field->is_required)<span class="text-red-500">*</span>@endif
                        </label>

                        @switch($field->type)
                            @case('text')
                                <input type="text" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
                                @break

                            @case('textarea')
                                <textarea id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition resize-none" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}></textarea>
                                @break

                            @case('email')
                                <input type="email" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
                                @break

                            @case('phone')
                                <input type="tel" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
                                @break

                            @case('date')
                                <input type="date" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" {{ $field->is_required ? 'required' : '' }}>
                                @break

                            @case('select')
                                <select id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition bg-white" {{ $field->is_required ? 'required' : '' }}>
                                    <option value="">{{ $field->placeholder ?: 'Select an option' }}</option>
                                    @if(is_array($field->options))
                                        @foreach($field->options as $option)
                                            <option value="{{ is_array($option) ? ($option['value'] ?? $option['label'] ?? $option) : $option }}">
                                                {{ is_array($option) ? ($option['label'] ?? $option['value'] ?? $option) : $option }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @break

                            @case('file')
                                <input type="file" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" {{ $field->is_required ? 'required' : '' }}>
                                @break

                            @default
                                <input type="text" id="custom_{{ $field->id }}" name="custom_{{ $field->id }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus-primary focus:outline-none transition" placeholder="{{ $field->placeholder }}" {{ $field->is_required ? 'required' : '' }}>
                        @endswitch

                        @if($field->help_text)
                        <p class="text-gray-500 text-xs mt-1">{{ $field->help_text }}</p>
                        @endif
                        <p class="text-red-500 text-sm mt-1 hidden" data-error="custom_{{ $field->id }}"></p>
                    </div>
                    @endforeach
                @endif

                <!-- Error Alert -->
                <div id="form-error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <p id="form-error-message"></p>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" id="submit-btn" class="w-full btn-primary text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                        <span id="submit-text">{{ $form->submit_button_text ?? 'Submit Ticket' }}</span>
                        <svg id="submit-spinner" class="hidden animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-500 text-sm">
            <p>Powered by {{ config('app.name') }}</p>
        </div>
    </div>

    <!-- Success Modal (for modal confirmation type) -->
    <div id="success-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeSuccessModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 relative z-10 text-center">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 id="modal-headline" class="text-xl font-bold text-gray-900 mb-2">Thank You!</h3>
                <p id="modal-message" class="text-gray-600 mb-4">Your ticket has been submitted successfully.</p>
                <p class="text-sm text-gray-500 mb-4">Your ticket number: <span id="ticket-number" class="font-mono font-semibold text-primary"></span></p>
                <button type="button" onclick="closeSuccessModal()" class="btn-primary text-white font-semibold py-2.5 px-6 rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Inline Success (for inline confirmation type) -->
    <div id="success-inline" class="hidden max-w-xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8 text-center">
            <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 id="inline-headline" class="text-2xl font-bold text-gray-900 mb-3">Thank You!</h2>
            <p id="inline-message" class="text-gray-600 mb-4">Your ticket has been submitted successfully.</p>
            <p class="text-sm text-gray-500 mb-6">Your ticket number: <span id="inline-ticket-number" class="font-mono font-semibold text-primary"></span></p>
            <button type="button" onclick="resetForm()" class="btn-primary text-white font-semibold py-3 px-8 rounded-lg transition">
                Submit Another Ticket
            </button>
        </div>
    </div>

    <script>
    const form = document.getElementById('ticket-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    const formError = document.getElementById('form-error');
    const formErrorMessage = document.getElementById('form-error-message');
    const successModal = document.getElementById('success-modal');
    const successInline = document.getElementById('success-inline');
    const formContainer = document.querySelector('.max-w-xl');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Clear previous errors
        clearErrors();

        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Submitting...';
        submitSpinner.classList.remove('hidden');

        try {
            const formData = new FormData(form);
            const response = await fetch('{{ route("public.ticket-form.submit", $form->slug) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                form.reset();
                handleSuccess(data);
            } else if (data.errors) {
                // Show validation errors
                for (const [field, messages] of Object.entries(data.errors)) {
                    const errorEl = document.querySelector(`[data-error="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                        errorEl.classList.remove('hidden');

                        // Add error styling to input
                        const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('border-red-500');
                        }
                    }
                }
            } else if (data.message) {
                showFormError(data.message);
            } else {
                showFormError('An error occurred. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            showFormError('An error occurred. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = '{{ $form->submit_button_text ?? "Submit Ticket" }}';
            submitSpinner.classList.add('hidden');
        }
    });

    function handleSuccess(data) {
        const confirmation = data.confirmation || {};
        const type = confirmation.type || 'inline';
        const headline = confirmation.headline || 'Thank You!';
        const message = confirmation.message || 'Your ticket has been submitted successfully.';
        const redirectUrl = confirmation.redirect_url;
        const ticketNumber = data.ticket_number;

        switch (type) {
            case 'redirect':
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    // Fallback to inline if no redirect URL
                    showInlineSuccess(headline, message, ticketNumber);
                }
                break;

            case 'modal':
                document.getElementById('modal-headline').textContent = headline;
                document.getElementById('modal-message').textContent = message;
                document.getElementById('ticket-number').textContent = '#' + ticketNumber;
                successModal.classList.remove('hidden');
                break;

            case 'inline':
            default:
                showInlineSuccess(headline, message, ticketNumber);
                break;
        }
    }

    function showInlineSuccess(headline, message, ticketNumber) {
        document.getElementById('inline-headline').textContent = headline;
        document.getElementById('inline-message').textContent = message;
        document.getElementById('inline-ticket-number').textContent = '#' + ticketNumber;

        // Hide form and show success
        formContainer.classList.add('hidden');
        successInline.classList.remove('hidden');
    }

    function resetForm() {
        successInline.classList.add('hidden');
        formContainer.classList.remove('hidden');
    }

    function clearErrors() {
        document.querySelectorAll('[data-error]').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        document.querySelectorAll('input, select, textarea').forEach(el => {
            el.classList.remove('border-red-500');
        });
        formError.classList.add('hidden');
    }

    function showFormError(message) {
        formErrorMessage.textContent = message;
        formError.classList.remove('hidden');
    }

    function closeSuccessModal() {
        successModal.classList.add('hidden');
    }
    </script>
</body>
</html>
