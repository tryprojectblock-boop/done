@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'placeholder' => 'Write something...',
    'label' => null,
    'required' => false,
    'height' => '200px',
])

@php
    $editorId = $id ?? 'quill-editor-' . uniqid();
@endphp

<div class="form-control quill-editor-wrapper">
    @if($label)
        <label class="label">
            <span class="label-text font-medium">{{ $label }} @if($required)<span class="text-error">*</span>@endif</span>
        </label>
    @endif

    <div class="border border-base-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2">
        <div id="{{ $editorId }}"
             class="quill-editor"
             data-quill-id="{{ $editorId }}"
             data-placeholder="{{ $placeholder }}"
             data-upload-url="{{ route('upload.image') }}"
             data-csrf="{{ csrf_token() }}"
             data-initial-content="{{ e($value) }}"
             style="min-height: {{ $height }};"></div>
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $editorId }}-input" value="{{ $value }}">
</div>

@once
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
.quill-editor-wrapper .ql-toolbar {
    border: none !important;
    border-bottom: 1px solid oklch(var(--bc) / 0.2) !important;
    background: oklch(var(--b2));
    padding: 8px 12px;
}

.quill-editor-wrapper .ql-container {
    border: none !important;
    font-family: inherit;
    font-size: 0.95rem;
}

.quill-editor-wrapper .ql-editor {
    padding: 12px 16px;
    min-height: 150px;
}

.quill-editor-wrapper .ql-editor.ql-blank::before {
    color: oklch(var(--bc) / 0.4);
    font-style: normal;
    left: 16px;
}

.quill-editor-wrapper .ql-editor img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 8px 0;
}

.quill-editor-wrapper .ql-editor p {
    margin-bottom: 0.5em;
}

.quill-editor-wrapper .ql-toolbar button:hover,
.quill-editor-wrapper .ql-toolbar button:focus,
.quill-editor-wrapper .ql-toolbar button.ql-active {
    color: oklch(var(--p)) !important;
}

.quill-editor-wrapper .ql-toolbar button:hover .ql-stroke,
.quill-editor-wrapper .ql-toolbar button:focus .ql-stroke,
.quill-editor-wrapper .ql-toolbar button.ql-active .ql-stroke {
    stroke: oklch(var(--p)) !important;
}

.quill-editor-wrapper .ql-toolbar button:hover .ql-fill,
.quill-editor-wrapper .ql-toolbar button:focus .ql-fill,
.quill-editor-wrapper .ql-toolbar button.ql-active .ql-fill {
    fill: oklch(var(--p)) !important;
}

.quill-editor-wrapper .ql-snow .ql-picker.ql-expanded .ql-picker-options {
    border-color: oklch(var(--bc) / 0.2);
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

/* Image upload loading state */
.quill-editor-wrapper .ql-editor .image-uploading {
    opacity: 0.5;
    position: relative;
}

.quill-editor-wrapper .ql-editor .image-uploading::after {
    content: 'Uploading...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
}

/* Drag and drop highlight */
.quill-editor-wrapper .ql-container.drag-over {
    background: oklch(var(--p) / 0.05);
}

.quill-editor-wrapper .ql-container.drag-over::after {
    content: 'Drop image here';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: oklch(var(--p));
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    pointer-events: none;
    z-index: 10;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
window.initQuillEditor = function(editorId, placeholder, uploadUrl, csrfToken, initialContent) {
    const editorElement = document.getElementById(editorId);
    const hiddenInput = document.getElementById(editorId + '-input');

    if (!editorElement || editorElement.quillInstance) return;

    initialContent = initialContent || '';

    // Custom image handler
    const imageHandler = function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (file) {
                await uploadImage(file, this.quill);
            }
        };
    };

    // Initialize Quill
    const quill = new Quill(editorElement, {
        theme: 'snow',
        placeholder: placeholder,
        modules: {
            toolbar: {
                container: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['link', 'image'],
                    ['blockquote', 'code-block'],
                    [{ 'color': [] }, { 'background': [] }],
                    ['clean']
                ],
                handlers: {
                    image: imageHandler
                }
            }
        }
    });

    // Store reference
    editorElement.quillInstance = quill;

    // Upload image function
    async function uploadImage(file, quillInstance) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be less than 5MB');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);

        // Get cursor position
        const range = quillInstance.getSelection(true);

        // Insert temporary placeholder
        quillInstance.insertText(range.index, 'Uploading image...', { 'color': '#999' });
        const placeholderLength = 'Uploading image...'.length;

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const result = await response.json();

            // Remove placeholder
            quillInstance.deleteText(range.index, placeholderLength);

            if (result.success) {
                // Insert image
                quillInstance.insertEmbed(range.index, 'image', result.url);
                quillInstance.setSelection(range.index + 1);
            } else {
                alert('Failed to upload image: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            // Remove placeholder
            quillInstance.deleteText(range.index, placeholderLength);
            console.error('Upload error:', error);
            alert('Failed to upload image. Please try again.');
        }
    }

    // Drag and drop support
    const container = editorElement.querySelector('.ql-container') || editorElement;

    container.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        container.classList.add('drag-over');
    });

    container.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        container.classList.remove('drag-over');
    });

    container.addEventListener('drop', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        container.classList.remove('drag-over');

        const files = e.dataTransfer.files;
        for (let i = 0; i < files.length; i++) {
            if (files[i].type.startsWith('image/')) {
                await uploadImage(files[i], quill);
            }
        }
    });

    // Paste image support
    editorElement.addEventListener('paste', async (e) => {
        const clipboardData = e.clipboardData || window.clipboardData;
        const items = clipboardData.items;

        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                e.preventDefault();
                const file = items[i].getAsFile();
                await uploadImage(file, quill);
                break;
            }
        }
    });

    // Set initial content if provided
    if (initialContent && initialContent.trim() !== '') {
        quill.root.innerHTML = initialContent;
    }

    // Sync content to hidden input
    quill.on('text-change', function() {
        const content = quill.root.innerHTML;
        hiddenInput.value = content === '<p><br></p>' ? '' : content;
    });

    // Initialize hidden input with current content
    const currentContent = quill.root.innerHTML;
    hiddenInput.value = currentContent === '<p><br></p>' ? '' : currentContent;

    return quill;
};

// Auto-initialize editors when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quill-editor[data-quill-id]').forEach(function(el) {
        const id = el.dataset.quillId;
        const placeholder = el.dataset.placeholder || 'Write something...';
        const uploadUrl = el.dataset.uploadUrl;
        const csrfToken = el.dataset.csrf;
        const initialContent = el.dataset.initialContent || '';
        window.initQuillEditor(id, placeholder, uploadUrl, csrfToken, initialContent);
    });
});
</script>
@endpush
@endonce
