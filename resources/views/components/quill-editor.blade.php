@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'placeholder' => 'Write something...',
    'label' => null,
    'required' => false,
    'height' => '200px',
    'mentions' => true,
    'emoji' => true,
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
             data-mentions-url="{{ route('mentions.search') }}"
             data-csrf="{{ csrf_token() }}"
             data-initial-content="{{ e($value) }}"
             data-enable-mentions="{{ $mentions ? 'true' : 'false' }}"
             data-enable-emoji="{{ $emoji ? 'true' : 'false' }}"
             style="min-height: {{ $height }};"></div>
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $editorId }}-input" value="{{ $value }}">
</div>

@once
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/quill-mention@6.0.1/dist/quill.mention.css" rel="stylesheet">
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

/* Mention styles */
.ql-mention-list-container {
    background-color: white;
    border: 1px solid oklch(var(--bc) / 0.2);
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    z-index: 99999 !important;
    width: 280px;
}

.ql-mention-list {
    list-style: none;
    margin: 0;
    padding: 4px;
    max-height: 200px;
    overflow-y: auto;
}

.ql-mention-list-item {
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.15s;
}

.ql-mention-list-item:hover,
.ql-mention-list-item.selected {
    background-color: oklch(var(--p) / 0.1);
}

.ql-mention-list-item .mention-item-custom,
.mention-item-custom {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ql-mention-list-item .mention-avatar,
.mention-item-custom .mention-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.ql-mention-list-item .mention-info,
.mention-item-custom .mention-info {
    flex: 1;
    min-width: 0;
}

.ql-mention-list-item .mention-name,
.mention-item-custom .mention-name {
    font-weight: 500;
    font-size: 14px;
    color: oklch(var(--bc));
}

.ql-mention-list-item .mention-email,
.mention-item-custom .mention-email {
    font-size: 12px;
    color: oklch(var(--bc) / 0.5);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Loading state in mention */
.ql-mention-loading {
    padding: 12px;
    text-align: center;
    color: oklch(var(--bc) / 0.5);
}

/* Mention in editor */
.mention {
    background-color: oklch(var(--p) / 0.15);
    color: oklch(var(--p));
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 500;
    text-decoration: none;
}

.mention:hover {
    background-color: oklch(var(--p) / 0.25);
}

/* Emoji button in toolbar */
.ql-emoji::before {
    content: '😀';
    font-size: 16px;
}

/* Emoji picker styles */
.emoji-picker {
    position: absolute;
    background: white;
    border: 1px solid oklch(var(--bc) / 0.2);
    border-radius: 12px;
    box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.2);
    padding: 12px;
    z-index: 9999;
    width: 320px;
}

.emoji-picker-header {
    display: flex;
    gap: 4px;
    padding-bottom: 8px;
    border-bottom: 1px solid oklch(var(--bc) / 0.1);
    margin-bottom: 8px;
    overflow-x: auto;
}

.emoji-category-btn {
    padding: 6px 10px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    transition: background-color 0.15s;
}

.emoji-category-btn:hover,
.emoji-category-btn.active {
    background-color: oklch(var(--p) / 0.1);
}

.emoji-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
    max-height: 200px;
    overflow-y: auto;
}

.emoji-btn {
    padding: 6px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    font-size: 20px;
    transition: background-color 0.15s, transform 0.1s;
}

.emoji-btn:hover {
    background-color: oklch(var(--p) / 0.1);
    transform: scale(1.15);
}

.emoji-search {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid oklch(var(--bc) / 0.2);
    border-radius: 6px;
    margin-bottom: 8px;
    font-size: 14px;
    outline: none;
}

.emoji-search:focus {
    border-color: oklch(var(--p));
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
// quill-mention 6.x for Quill 2.x compatibility
// The module self-registers when loaded
</script>
<script src="https://cdn.jsdelivr.net/npm/quill-mention@6.0.1/dist/quill.mention.js"></script>
<script>
// Emoji data
const emojiData = {
    'Smileys': ['😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊', '😇', '🙂', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓', '🧐', '😕', '😟', '🙁', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖'],
    'Gestures': ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '👀', '👁️', '👅', '👄'],
    'Hearts': ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❤️‍🔥', '❤️‍🩹', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '♥️', '💌', '💋', '💯', '💢', '💥', '💫', '💦', '💨', '🕳️', '💣', '💬', '👁️‍🗨️', '🗨️', '🗯️', '💭', '💤'],
    'Objects': ['⌚', '📱', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '💽', '💾', '💿', '📀', '📼', '📷', '📸', '📹', '🎥', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙️', '🎚️', '🎛️', '🧭', '⏱️', '⏲️', '⏰', '🕰️', '⌛', '⏳', '📡', '🔋', '🔌', '💡', '🔦', '🕯️', '🪔', '🧯', '🛢️', '💸', '💵', '💴', '💶', '💷', '💰', '💳', '💎', '⚖️', '🧰', '🔧', '🔨', '⚒️', '🛠️', '⛏️', '🔩', '⚙️', '🧱', '⛓️', '🧲', '🔫', '💣', '🧨', '🪓', '🔪', '🗡️', '⚔️', '🛡️', '🚬', '⚰️', '⚱️', '🏺', '🔮', '📿', '🧿', '💈', '⚗️', '🔭', '🔬', '🕳️', '🩹', '🩺', '💊', '💉', '🩸', '🧬', '🦠', '🧫', '🧪'],
    'Nature': ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🦟', '🦗', '🕷️', '🕸️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺', '🐈', '🐈‍⬛', '🐓', '🦃', '🦚', '🦜', '🦢', '🦩', '🐇', '🦝', '🦨', '🦡', '🦦', '🦥', '🐁', '🐀', '🐿️', '🦔'],
    'Food': ['🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🫐', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶️', '🫑', '🌽', '🥕', '🫒', '🧄', '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🫓', '🥪', '🥙', '🧆', '🌮', '🌯', '🫔', '🥗', '🥘', '🫕', '🥫', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙', '🍚', '🍘', '🍥', '🥠', '🥮', '🍢', '🍡', '🍧', '🍨', '🍦', '🥧', '🧁', '🍰', '🎂', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩', '🍪', '🌰', '🥜', '🍯', '🥛', '🍼', '🫖', '☕', '🍵', '🧃', '🥤', '🧋', '🍶', '🍺', '🍻', '🥂', '🍷', '🥃', '🍸', '🍹', '🧉', '🍾', '🧊']
};

// Create emoji picker HTML
function createEmojiPicker(quill, editorId) {
    const picker = document.createElement('div');
    picker.className = 'emoji-picker';
    picker.id = editorId + '-emoji-picker';
    picker.style.display = 'none';

    let html = '<input type="text" class="emoji-search" placeholder="Search emojis...">';
    html += '<div class="emoji-picker-header">';

    const categories = Object.keys(emojiData);
    categories.forEach((cat, i) => {
        const firstEmoji = emojiData[cat][0];
        html += `<button type="button" class="emoji-category-btn ${i === 0 ? 'active' : ''}" data-category="${cat}">${firstEmoji}</button>`;
    });

    html += '</div>';
    html += '<div class="emoji-grid" id="' + editorId + '-emoji-grid">';

    // Default to first category
    emojiData[categories[0]].forEach(emoji => {
        html += `<button type="button" class="emoji-btn" data-emoji="${emoji}">${emoji}</button>`;
    });

    html += '</div>';
    picker.innerHTML = html;

    // Event listeners
    picker.querySelectorAll('.emoji-category-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const category = btn.dataset.category;
            picker.querySelectorAll('.emoji-category-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const grid = picker.querySelector('.emoji-grid');
            grid.innerHTML = emojiData[category].map(emoji =>
                `<button type="button" class="emoji-btn" data-emoji="${emoji}">${emoji}</button>`
            ).join('');

            // Re-attach click handlers
            grid.querySelectorAll('.emoji-btn').forEach(emojiBtn => {
                emojiBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    insertEmoji(quill, emojiBtn.dataset.emoji, picker);
                });
            });
        });
    });

    picker.querySelectorAll('.emoji-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            insertEmoji(quill, btn.dataset.emoji, picker);
        });
    });

    // Search functionality
    const searchInput = picker.querySelector('.emoji-search');
    searchInput.addEventListener('input', (e) => {
        const search = e.target.value.toLowerCase();
        const grid = picker.querySelector('.emoji-grid');

        if (!search) {
            // Show first category
            const firstCat = Object.keys(emojiData)[0];
            grid.innerHTML = emojiData[firstCat].map(emoji =>
                `<button type="button" class="emoji-btn" data-emoji="${emoji}">${emoji}</button>`
            ).join('');
        } else {
            // Search all emojis
            let results = [];
            Object.values(emojiData).forEach(emojis => {
                results = results.concat(emojis);
            });
            grid.innerHTML = results.slice(0, 64).map(emoji =>
                `<button type="button" class="emoji-btn" data-emoji="${emoji}">${emoji}</button>`
            ).join('');
        }

        // Re-attach click handlers
        grid.querySelectorAll('.emoji-btn').forEach(emojiBtn => {
            emojiBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                insertEmoji(quill, emojiBtn.dataset.emoji, picker);
            });
        });
    });

    // Prevent clicks inside picker from closing it
    picker.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    return picker;
}

function insertEmoji(quill, emoji, picker) {
    const range = quill.getSelection(true);
    quill.insertText(range.index, emoji);
    quill.setSelection(range.index + emoji.length);
    picker.style.display = 'none';
}

// Register Quill Mention module (if available)
(function() {
    if (typeof Quill === 'undefined') {
        console.error('Quill is not loaded');
        return;
    }

    // quill-mention 6.x auto-registers itself, but let's verify
    if (typeof window.Mention !== 'undefined') {
        console.log('Mention module detected:', window.Mention);
    } else if (typeof QuillMention !== 'undefined') {
        console.log('QuillMention detected, registering...');
        Quill.register('modules/mention', QuillMention.default || QuillMention, true);
    } else {
        console.log('Looking for mention module in Quill.imports...');
        console.log('Quill imports:', Object.keys(Quill.imports || {}));
    }
})();

window.initQuillEditor = function(editorId, placeholder, uploadUrl, csrfToken, initialContent) {
    const editorElement = document.getElementById(editorId);
    const hiddenInput = document.getElementById(editorId + '-input');

    if (!editorElement || editorElement.quillInstance) return;

    if (!hiddenInput) {
        console.error('Hidden input not found for editor:', editorId + '-input');
        return;
    }

    initialContent = initialContent || '';
    const mentionsUrl = editorElement.dataset.mentionsUrl;
    const enableMentions = editorElement.dataset.enableMentions === 'true';
    const enableEmoji = editorElement.dataset.enableEmoji === 'true';

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

    // Build toolbar config
    const toolbarContainer = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'image'],
        ['blockquote', 'code-block'],
        [{ 'color': [] }, { 'background': [] }],
    ];

    if (enableEmoji) {
        toolbarContainer.push(['emoji']);
    }

    toolbarContainer.push(['clean']);

    // Build modules config
    const modules = {
        toolbar: {
            container: toolbarContainer,
            handlers: {
                image: imageHandler
            }
        }
    };

    // Add mention module if enabled
    if (enableMentions && mentionsUrl) {
        modules.mention = {
            allowedChars: /^[A-Za-z0-9\sÅÄÖåäö_-]*$/,
            mentionDenotationChars: ["@"],
            positioningStrategy: 'fixed',
            spaceAfterInsert: true,
            showDenotationChar: true,
            minChars: 0,
            maxChars: 31,
            offsetTop: 2,
            offsetLeft: 0,
            isolateCharacter: false,
            fixMentionsToQuill: false,
            defaultMenuOrientation: 'bottom',
            dataAttributes: ['id', 'value', 'email', 'avatar'],
            source: function(searchTerm, renderList, mentionChar) {
                fetch(`${mentionsUrl}?search=${encodeURIComponent(searchTerm)}`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(users => {
                    renderList(users, searchTerm);
                })
                .catch(error => {
                    console.error('Mention search error:', error);
                    renderList([], searchTerm);
                });
            },
            renderItem: function(item, searchTerm) {
                const div = document.createElement('div');
                div.className = 'mention-item-custom';
                div.innerHTML = `
                    <img src="${item.avatar || '/images/default-avatar.png'}" alt="${item.value}" class="mention-avatar">
                    <div class="mention-info">
                        <div class="mention-name">${item.value}</div>
                        <div class="mention-email">${item.email || ''}</div>
                    </div>
                `;
                return div;
            },
            renderLoading: function() {
                return 'Loading...';
            }
        };
    }

    // Initialize Quill
    const quill = new Quill(editorElement, {
        theme: 'snow',
        placeholder: placeholder,
        modules: modules
    });

    // Add emoji picker if enabled
    if (enableEmoji) {
        const toolbar = quill.getModule('toolbar');
        const emojiBtn = editorElement.parentElement.querySelector('.ql-emoji');

        if (emojiBtn) {
            const emojiPicker = createEmojiPicker(quill, editorId);
            editorElement.parentElement.appendChild(emojiPicker);

            emojiBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const isVisible = emojiPicker.style.display === 'block';
                emojiPicker.style.display = isVisible ? 'none' : 'block';

                if (!isVisible) {
                    // Position the picker
                    const btnRect = emojiBtn.getBoundingClientRect();
                    const containerRect = editorElement.parentElement.getBoundingClientRect();
                    emojiPicker.style.top = (btnRect.bottom - containerRect.top + 5) + 'px';
                    emojiPicker.style.right = '10px';
                }
            });

            // Close picker when clicking outside
            document.addEventListener('click', (e) => {
                if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
                    emojiPicker.style.display = 'none';
                }
            });
        }
    }

    // Store reference
    editorElement.quillInstance = quill;

    // Upload image function
    async function uploadImage(file, quillInstance) {
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be less than 5MB');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);

        const range = quillInstance.getSelection(true);
        quillInstance.insertText(range.index, 'Uploading image...', { 'color': '#999' });
        const placeholderLength = 'Uploading image...'.length;

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const result = await response.json();
            quillInstance.deleteText(range.index, placeholderLength);

            if (result.success) {
                quillInstance.insertEmbed(range.index, 'image', result.url);
                quillInstance.setSelection(range.index + 1);
            } else {
                alert('Failed to upload image: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
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

    // Also sync on form submit to ensure latest content
    const form = hiddenInput.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            const content = quill.root.innerHTML;
            hiddenInput.value = content === '<p><br></p>' ? '' : content;
        });
    }

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
