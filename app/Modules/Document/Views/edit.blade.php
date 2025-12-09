@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.index') }}" class="hover:text-primary">Documents</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.show', $document->uuid) }}" class="hover:text-primary">{{ Str::limit($document->title, 20) }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Settings</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Document Settings</h1>
            <p class="text-base-content/60">Update document details</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('documents.update', $document->uuid) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card: Document Details -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--file-text] size-5"></span>
                        Document Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Document Title -->
                        <div class="form-control">
                            <label class="label" for="document-title">
                                <span class="label-text font-medium">Document Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="document-title" value="{{ old('title', $document->title) }}"
                                   class="input input-bordered w-full" placeholder="Enter document name" required>
                        </div>

                        <!-- Short Description -->
                        <div class="form-control">
                            <label class="label" for="document-description">
                                <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <textarea name="description" id="document-description" rows="2"
                                      class="textarea textarea-bordered w-full" placeholder="Brief description of this document">{{ old('description', $document->description) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-base-200/50 rounded-lg">
                        <p class="text-sm text-base-content/70">
                            <span class="icon-[tabler--info-circle] size-4 inline-block mr-1"></span>
                            <strong>Tip:</strong> Use <code class="bg-base-300 px-1 rounded">@username</code> in the document to mention and notify team members.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row gap-3 justify-start">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--device-floppy] size-5"></span>
                            Save Changes
                        </button>
                        <a href="{{ route('documents.show', $document->uuid) }}" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Danger Zone -->
        @if($document->canDelete(auth()->user()))
        <div class="card bg-base-100 shadow border border-error/20 mt-6">
            <div class="card-body">
                <h2 class="card-title text-lg text-error mb-4">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    Danger Zone
                </h2>
                <p class="text-sm text-base-content/70 mb-4">
                    Once you delete a document, there is no going back. Please be certain.
                </p>
                <form action="{{ route('documents.destroy', $document->uuid) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-outline">
                        <span class="icon-[tabler--trash] size-5"></span>
                        Delete Document
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
