@if(session('success'))
    <div class="alert alert-success" data-auto-dismiss="5000">
        <span class="icon-[tabler--circle-check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error" data-auto-dismiss="5000">
        <span class="icon-[tabler--alert-circle] size-5"></span>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning" data-auto-dismiss="5000">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <span>{{ session('warning') }}</span>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info" data-auto-dismiss="5000">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <span>{{ session('info') }}</span>
    </div>
@endif
