@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    @include('discussion::channels.partials.sidebar')

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col bg-base-100">
        <!-- Header -->
        <div class="border-b border-base-200 px-4 md:px-6 py-2 sticky top-16 z-10 bg-base-100">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-1 text-xs text-base-content/60 mb-1">
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.index') }}" class="hover:text-primary">Channels</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.show', $channel) }}" class="hover:text-primary">{{ $channel->name }}</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content">New Thread</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--hash] size-5"></span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-base-content">New Thread</h1>
                    <p class="text-sm text-base-content/60">Post to {{ $channel->tag }}</p>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            <div class="max-w-3xl mx-auto">
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

                <form action="{{ route('channels.threads.store', $channel) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Thread Title -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="What do you want to discuss?" class="input input-bordered w-full text-lg @error('title') input-error @enderror" required maxlength="255" autofocus />
                        @error('title')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Thread Content (Rich Text Editor) -->
                    <x-quill-editor
                        name="content"
                        id="thread-content"
                        label="Details"
                        :value="old('content')"
                        placeholder="Add more context or details... You can drag & drop images here"
                        height="250px"
                    />

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                        <a href="{{ route('channels.show', $channel) }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--send] size-4"></span>
                            Post Thread
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
