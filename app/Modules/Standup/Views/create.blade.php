@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('standups.index', $workspace) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-base-content">Submit Standup</h1>
                <p class="text-base-content/60">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    <h3 class="font-bold">Validation Error</h3>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('standups.store', $workspace) }}" method="POST">
            @csrf

            <div class="card bg-base-100 shadow">
                <div class="card-body space-y-6">
                    @foreach($template->getOrderedQuestions() as $question)
                        @if($question['type'] !== 'mood')
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">
                                        {{ $question['question'] }}
                                        @if($question['required'])
                                            <span class="text-error">*</span>
                                        @endif
                                    </span>
                                </label>
                                <textarea
                                    name="responses[{{ $question['id'] }}]"
                                    class="textarea textarea-bordered min-h-24 @error('responses.' . $question['id']) textarea-error @enderror"
                                    placeholder="Type your response..."
                                    {{ $question['required'] ? 'required' : '' }}
                                >{{ old('responses.' . $question['id']) }}</textarea>
                                @if($question['type'] === 'blockers')
                                    <label class="label">
                                        <span class="label-text-alt text-base-content/60">Leave empty if you have no blockers</span>
                                    </label>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <!-- Mood Selector -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">How are you feeling today?</span>
                        </label>
                        <div class="flex items-center gap-2 flex-wrap">
                            @foreach($moodOptions as $option)
                                <label class="cursor-pointer">
                                    <input type="radio" name="mood" value="{{ $option['value'] }}"
                                           class="hidden peer"
                                           {{ old('mood') === $option['value'] ? 'checked' : '' }} />
                                    <div class="flex flex-col items-center p-3 rounded-lg border-2 border-base-300 peer-checked:border-primary peer-checked:bg-primary/10 hover:border-primary/50 transition-colors">
                                        <span class="text-3xl">{{ $option['emoji'] }}</span>
                                        <span class="text-xs mt-1 text-base-content/70">{{ $option['label'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- On Track Status -->
                    @php
                        $defaultOnTrack = old('is_on_track', $tracker->is_on_track ? '1' : '0');
                        $defaultReason = old('off_track_reason', $tracker->off_track_reason ?? '');
                    @endphp
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Are you on track with your work?</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="cursor-pointer flex items-center gap-2">
                                <input type="radio" name="is_on_track" value="1" class="radio radio-success"
                                       {{ $defaultOnTrack === '1' ? 'checked' : '' }}
                                       onchange="toggleOffTrackNotes()" />
                                <span class="label-text flex items-center gap-1">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                    Yes, on track
                                </span>
                            </label>
                            <label class="cursor-pointer flex items-center gap-2">
                                <input type="radio" name="is_on_track" value="0" class="radio radio-error"
                                       {{ $defaultOnTrack === '0' ? 'checked' : '' }}
                                       onchange="toggleOffTrackNotes()" />
                                <span class="label-text flex items-center gap-1">
                                    <span class="icon-[tabler--alert-circle] size-4 text-error"></span>
                                    No, off track
                                </span>
                            </label>
                        </div>
                        <div id="off-track-notes" class="mt-3 {{ $defaultOnTrack === '0' ? '' : 'hidden' }}">
                            <textarea name="off_track_reason" class="textarea textarea-bordered w-full"
                                      placeholder="What's slowing you down or causing delays?"
                                      rows="2">{{ $defaultReason }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">This helps your team understand how to support you</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-4 pt-4 border-t border-base-200">
                        <a href="{{ route('standups.index', $workspace) }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Submit Standup
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleOffTrackNotes() {
    const notesDiv = document.getElementById('off-track-notes');
    const isOnTrack = document.querySelector('input[name="is_on_track"]:checked')?.value;

    if (isOnTrack === '0') {
        notesDiv.classList.remove('hidden');
    } else {
        notesDiv.classList.add('hidden');
    }
}
</script>
@endsection
