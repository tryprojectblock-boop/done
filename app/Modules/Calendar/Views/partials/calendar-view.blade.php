<div class="card bg-base-100 shadow">
    <div class="card-body p-4">
        <div id="fullcalendar" class="fc-custom"></div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .fc-custom {
        --fc-border-color: oklch(var(--bc) / 0.15);
        --fc-today-bg-color: oklch(var(--p) / 0.05);
        --fc-event-border-color: transparent;
        --fc-page-bg-color: transparent;
    }

    .fc-custom .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 600;
    }

    .fc-custom .fc-button {
        background-color: oklch(var(--b2)) !important;
        border: 1px solid oklch(var(--bc) / 0.2) !important;
        color: oklch(var(--bc)) !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        text-transform: none !important;
    }

    .fc-custom .fc-button:hover {
        background-color: oklch(var(--bc) / 0.1) !important;
    }

    .fc-custom .fc-button-primary:not(:disabled).fc-button-active,
    .fc-custom .fc-button-primary:not(:disabled):active {
        background-color: oklch(var(--p)) !important;
        border-color: oklch(var(--p)) !important;
        color: oklch(var(--pc)) !important;
    }

    .fc-custom .fc-col-header-cell {
        padding: 0.75rem 0;
        font-weight: 500;
        background-color: oklch(var(--b2));
    }

    .fc-custom .fc-daygrid-day {
        min-height: 100px;
    }

    .fc-custom .fc-daygrid-day-number {
        padding: 8px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .fc-custom .fc-day-today .fc-daygrid-day-number {
        background-color: oklch(var(--p));
        color: oklch(var(--pc));
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fc-custom .fc-event {
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        cursor: pointer;
        margin: 1px 2px;
    }

    .fc-custom .fc-event:hover {
        filter: brightness(0.9);
    }

    .fc-custom .fc-daygrid-more-link {
        font-size: 0.75rem;
        font-weight: 500;
        color: oklch(var(--p));
    }

    .fc-custom .fc-popover {
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid oklch(var(--bc) / 0.15);
    }

    .fc-custom .fc-popover-header {
        background-color: oklch(var(--b2));
        padding: 8px 12px;
        font-weight: 500;
    }

    /* Custom event content */
    .fc-event-custom {
        display: flex;
        align-items: center;
        gap: 4px;
        overflow: hidden;
    }

    .fc-event-custom .event-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .fc-event-custom .event-title {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fc-event-custom .event-avatar {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-left: auto;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('fullcalendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '{{ $startOfMonth->format('Y-m-d') }}',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        height: 'auto',
        dayMaxEvents: 3,
        eventDisplay: 'block',
        displayEventTime: false,

        events: function(info, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                workspace_id: '{{ $filters['workspace_id'] ?? '' }}',
                assignee_id: '{{ $filters['assignee_id'] ?? '' }}'
            });

            fetch(`{{ route('calendar.events') }}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => successCallback(data))
            .catch(error => {
                console.error('Error fetching events:', error);
                failureCallback(error);
            });
        },

        eventContent: function(arg) {
            const props = arg.event.extendedProps;
            const container = document.createElement('div');
            container.className = 'fc-event-custom';

            // Status dot
            const dot = document.createElement('span');
            dot.className = 'event-dot';
            dot.style.backgroundColor = props.is_overdue ? '#ef4444' : (props.status_color || '#3b82f6');
            container.appendChild(dot);

            // Title
            const title = document.createElement('span');
            title.className = 'event-title';
            title.textContent = arg.event.title;
            container.appendChild(title);

            // Assignee avatar
            if (props.assignee_avatar) {
                const avatar = document.createElement('img');
                avatar.className = 'event-avatar';
                avatar.src = props.assignee_avatar;
                avatar.alt = props.assignee || '';
                container.appendChild(avatar);
            }

            return { domNodes: [container] };
        },

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const uuid = info.event.extendedProps.uuid;
            openTaskDrawer(uuid);
        },

        dateClick: function(info) {
            // Optional: Could open a modal to create a new task for this date
            console.log('Date clicked:', info.dateStr);
        },

        datesSet: function(info) {
            // Update URL when navigating (optional)
            // Could sync with the month/year filters
        }
    });

    calendar.render();

    // Store reference for potential external access
    window.fullCalendar = calendar;
});
</script>
@endpush
