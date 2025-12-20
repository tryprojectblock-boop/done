<div class="tabs tabs-bordered mb-6">
    <a href="{{ route('standups.index', $workspace) }}"
       class="tab tab-lg {{ $activeTab === 'standup' ? 'tab-active' : '' }}">
        <span class="icon-[tabler--checkbox] size-5 mr-2"></span>
        Daily Standup
    </a>
    <a href="{{ route('standups.tracker.index', $workspace) }}"
       class="tab tab-lg {{ $activeTab === 'tracker' ? 'tab-active' : '' }}">
        <span class="icon-[tabler--chart-dots] size-5 mr-2"></span>
        Tracker
    </a>
</div>
