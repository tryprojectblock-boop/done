<div
    x-data="fileUploadComponent({
        name: '{{ $name }}',
        multiple: {{ $multiple ? 'true' : 'false' }},
        maxSize: {{ $maxSize }},
        maxFiles: {{ $maxFiles }},
        preview: {{ $preview ? 'true' : 'false' }},
        uploadUrl: '{{ $uploadUrl }}',
        deleteUrl: '{{ $deleteUrl }}',
        existingFiles: {{ json_encode($existingFiles) }},
        accept: '{{ $acceptedTypes() }}',
        context: '{{ $context }}',
    })"
    class="file-upload-component"
>
    {{-- Label --}}
    @if($label)
        <label for="{{ $id }}" class="label label-text mb-2">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    {{-- Drop Zone --}}
    <div
        x-on:dragover.prevent="isDragging = true"
        x-on:dragleave.prevent="isDragging = false"
        x-on:drop.prevent="handleDrop($event)"
        x-bind:class="{ 'border-primary bg-primary/5': isDragging, 'border-base-300': !isDragging }"
        class="relative border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer hover:border-primary hover:bg-base-200/50"
        x-on:click="$refs.fileInput.click()"
        @if($disabled) x-bind:class="'opacity-50 pointer-events-none'" @endif
    >
        {{-- Hidden File Input --}}
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $multiple ? $name . '[]' : $name }}"
            x-ref="fileInput"
            x-on:change="handleFileSelect($event)"
            accept="{{ $acceptedTypes() }}"
            {{ $multiple ? 'multiple' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="hidden"
        >

        {{-- Upload Icon & Text --}}
        <div x-show="!isUploading" class="space-y-2">
            <div class="flex justify-center">
                <span class="icon-[tabler--cloud-upload] size-12 text-base-content/40"></span>
            </div>
            <div class="text-base-content">
                <span class="font-medium text-primary">Click to upload</span>
                <span class="text-base-content/60">or drag and drop</span>
            </div>
            <p class="text-sm text-base-content/50">
                {{ $hint ?: "Max file size: {$maxSizeFormatted()}" }}
                @if($multiple)
                    <br>Maximum {{ $maxFiles }} files
                @endif
            </p>
        </div>

        {{-- Upload Progress --}}
        <div x-show="isUploading" x-cloak class="space-y-2">
            <div class="flex justify-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>
            <p class="text-sm text-base-content">Uploading... <span x-text="uploadProgress + '%'"></span></p>
            <div class="w-full bg-base-300 rounded-full h-2">
                <div
                    class="bg-primary h-2 rounded-full transition-all duration-300"
                    x-bind:style="'width: ' + uploadProgress + '%'"
                ></div>
            </div>
        </div>
    </div>

    {{-- Error Messages --}}
    <template x-if="errors.length > 0">
        <div class="mt-2 space-y-1">
            <template x-for="error in errors" :key="error">
                <p class="text-sm text-error flex items-center gap-1">
                    <span class="icon-[tabler--alert-circle] size-4"></span>
                    <span x-text="error"></span>
                </p>
            </template>
        </div>
    </template>

    {{-- File Preview List --}}
    <template x-if="files.length > 0 && preview">
        <div class="mt-4 space-y-2">
            <template x-for="(file, index) in files" :key="file.id || index">
                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg group">
                    {{-- Preview Thumbnail --}}
                    <div class="shrink-0">
                        <template x-if="file.previewUrl && isImage(file)">
                            <img
                                x-bind:src="file.previewUrl"
                                x-bind:alt="file.name"
                                class="size-12 object-cover rounded"
                            >
                        </template>
                        <template x-if="!file.previewUrl || !isImage(file)">
                            <div class="size-12 flex items-center justify-center bg-base-300 rounded">
                                <span x-bind:class="getFileIcon(file)" class="size-6 text-base-content/60"></span>
                            </div>
                        </template>
                    </div>

                    {{-- File Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-base-content truncate" x-text="file.name"></p>
                        <p class="text-xs text-base-content/60" x-text="formatFileSize(file.size)"></p>
                    </div>

                    {{-- Status / Actions --}}
                    <div class="shrink-0 flex items-center gap-2">
                        {{-- Upload Progress for individual file --}}
                        <template x-if="file.uploading">
                            <span class="loading loading-spinner loading-sm text-primary"></span>
                        </template>

                        {{-- Success indicator --}}
                        <template x-if="file.uploaded && !file.uploading">
                            <span class="icon-[tabler--check] size-5 text-success"></span>
                        </template>

                        {{-- Error indicator --}}
                        <template x-if="file.error">
                            <span class="icon-[tabler--alert-circle] size-5 text-error" x-bind:title="file.error"></span>
                        </template>

                        {{-- Remove button --}}
                        <button
                            type="button"
                            x-on:click.stop="removeFile(index)"
                            class="btn btn-ghost btn-sm btn-square opacity-0 group-hover:opacity-100 transition-opacity"
                            x-bind:disabled="file.uploading"
                        >
                            <span class="icon-[tabler--x] size-4"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Hidden input for uploaded file paths (for form submission) --}}
    <template x-for="file in files.filter(f => f.uploaded && f.path)" :key="file.path">
        <input type="hidden" x-bind:name="'{{ $name }}_paths[]'" x-bind:value="file.path">
    </template>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('fileUploadComponent', (config) => ({
        files: config.existingFiles || [],
        isDragging: false,
        isUploading: false,
        uploadProgress: 0,
        errors: [],

        init() {
            // Initialize existing files with required properties
            this.files = this.files.map(file => ({
                ...file,
                uploaded: true,
                uploading: false,
                error: null,
            }));
        },

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            this.processFiles(files);
        },

        handleFileSelect(event) {
            const files = event.target.files;
            this.processFiles(files);
            event.target.value = ''; // Reset input
        },

        async processFiles(fileList) {
            this.errors = [];

            const files = Array.from(fileList);

            // Validate file count
            if (config.multiple) {
                const totalFiles = this.files.length + files.length;
                if (totalFiles > config.maxFiles) {
                    this.errors.push(`Maximum ${config.maxFiles} files allowed`);
                    return;
                }
            } else {
                this.files = []; // Replace existing file for single upload
            }

            for (const file of files) {
                // Validate file size
                if (file.size > config.maxSize) {
                    this.errors.push(`${file.name} exceeds maximum size`);
                    continue;
                }

                // Validate file type
                if (config.accept && !this.isAcceptedType(file)) {
                    this.errors.push(`${file.name} is not an accepted file type`);
                    continue;
                }

                const fileObj = {
                    id: Date.now() + Math.random(),
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    file: file,
                    previewUrl: null,
                    uploaded: false,
                    uploading: false,
                    error: null,
                    path: null,
                };

                // Generate preview for images
                if (this.isImage(fileObj)) {
                    fileObj.previewUrl = URL.createObjectURL(file);
                }

                this.files.push(fileObj);

                // Auto-upload if URL provided
                if (config.uploadUrl) {
                    await this.uploadFile(fileObj);
                } else {
                    fileObj.uploaded = true; // Mark as ready for form submission
                }
            }
        },

        async uploadFile(fileObj) {
            fileObj.uploading = true;
            this.isUploading = true;

            const formData = new FormData();
            formData.append('file', fileObj.file);
            formData.append('context', config.context);

            try {
                const response = await fetch(config.uploadUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (!response.ok) {
                    throw new Error('Upload failed');
                }

                const data = await response.json();

                fileObj.uploaded = true;
                fileObj.path = data.path;
                fileObj.url = data.url;
            } catch (error) {
                fileObj.error = error.message || 'Upload failed';
                this.errors.push(`Failed to upload ${fileObj.name}`);
            } finally {
                fileObj.uploading = false;
                this.isUploading = this.files.some(f => f.uploading);
                this.uploadProgress = 0;
            }
        },

        async removeFile(index) {
            const file = this.files[index];

            // If uploaded and delete URL provided, delete from server
            if (file.uploaded && file.path && config.deleteUrl) {
                try {
                    await fetch(config.deleteUrl, {
                        method: 'DELETE',
                        body: JSON.stringify({ path: file.path }),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });
                } catch (error) {
                    console.error('Failed to delete file:', error);
                }
            }

            // Revoke object URL if it exists
            if (file.previewUrl && file.previewUrl.startsWith('blob:')) {
                URL.revokeObjectURL(file.previewUrl);
            }

            this.files.splice(index, 1);
        },

        isImage(file) {
            return file.type?.startsWith('image/');
        },

        isAcceptedType(file) {
            if (!config.accept) return true;

            const acceptedTypes = config.accept.split(',').map(t => t.trim());

            return acceptedTypes.some(type => {
                if (type.startsWith('.')) {
                    return file.name.toLowerCase().endsWith(type.toLowerCase());
                }
                if (type.endsWith('/*')) {
                    return file.type.startsWith(type.replace('/*', '/'));
                }
                return file.type === type;
            });
        },

        getFileIcon(file) {
            const type = file.type || '';

            if (type.includes('pdf')) return 'icon-[tabler--file-type-pdf]';
            if (type.includes('word') || type.includes('document')) return 'icon-[tabler--file-type-doc]';
            if (type.includes('sheet') || type.includes('excel')) return 'icon-[tabler--file-type-xls]';
            if (type.includes('presentation') || type.includes('powerpoint')) return 'icon-[tabler--file-type-ppt]';
            if (type.includes('zip') || type.includes('archive')) return 'icon-[tabler--file-zip]';
            if (type.includes('image')) return 'icon-[tabler--photo]';

            return 'icon-[tabler--file]';
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
        },
    }));
});
</script>
@endPushOnce
