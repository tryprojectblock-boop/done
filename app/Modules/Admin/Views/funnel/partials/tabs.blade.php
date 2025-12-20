<!-- Funnel Tabs -->
<div class="tabs tabs-bordered mb-6">
    <a href="{{ route('backoffice.funnel.index') }}"
       class="tab tab-lg {{ request()->routeIs('backoffice.funnel.index') || request()->routeIs('backoffice.funnel.create') || request()->routeIs('backoffice.funnel.edit') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--filter] size-5 mr-2"></span>
        Funnel Builder
    </a>
    <a href="{{ route('backoffice.funnel.logs') }}"
       class="tab tab-lg {{ request()->routeIs('backoffice.funnel.logs*') ? 'tab-active' : '' }}">
        <span class="icon-[tabler--mail] size-5 mr-2"></span>
        Email Logs
    </a>
</div>
