<div class="card bg-base-100 shadow">
    <div class="card-body p-4">
        <div id="calendar-container" class="min-h-96"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar-container');

    if (!calendarEl) {
        console.error('Calendar container not found');
        return;
    }

    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar not loaded');
        calendarEl.innerHTML = '<div class="alert alert-error">Failed to load calendar library</div>';
        return;
    }

    try {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: '{{ $startOfMonth->format('Y-m-d') }}',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            height: 'auto',
            dayMaxEvents: 3,
            eventDisplay: 'block',
            displayEventTime: false,
            nowIndicator: true,
            editable: false,
            selectable: true,

            events: function(info, successCallback, failureCallback) {
                const params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr,
                    workspace_id: '{{ $filters['workspace_id'] ?? '' }}',
                    assignee_id: '{{ $filters['assignee_id'] ?? '' }}'
                });

                console.log('Fetching events:', `{{ route('calendar.events') }}?${params}`);

                fetch(`{{ route('calendar.events') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Events received:', data.length, data);

                    // Map events to use FlyonUI classes
                    const events = data.map(event => {
                        let className = 'fc-event-primary';

                        if (event.extendedProps && event.extendedProps.is_closed) {
                            className = 'fc-event-secondary';
                        } else if (event.extendedProps && event.extendedProps.is_overdue) {
                            className = 'fc-event-error';
                        } else if (event.extendedProps && event.extendedProps.priority) {
                            // Map priority to color class
                            switch(event.extendedProps.priority) {
                                case 'highest':
                                case 'high':
                                    className = 'fc-event-error';
                                    break;
                                case 'medium':
                                    className = 'fc-event-warning';
                                    break;
                                case 'low':
                                case 'lowest':
                                    className = 'fc-event-success';
                                    break;
                                default:
                                    className = 'fc-event-primary';
                            }
                        }

                        return {
                            ...event,
                            classNames: [className]
                        };
                    });
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
            },

            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const uuid = info.event.extendedProps.uuid;
                if (typeof openTaskDrawer === 'function') {
                    openTaskDrawer(uuid);
                } else {
                    window.location.href = '{{ url('/tasks') }}/' + uuid;
                }
            },

            dateClick: function(info) {
                console.log('Date clicked:', info.dateStr);
            },

            datesSet: function(info) {
                // Update URL when navigating (optional)
            }
        });

        calendar.render();
        console.log('Calendar rendered');

        // Store reference for potential external access
        window.fullCalendar = calendar;
    } catch (error) {
        console.error('Calendar initialization error:', error);
        calendarEl.innerHTML = '<div class="alert alert-error">Failed to initialize calendar: ' + error.message + '</div>';
    }
});
</script>
@endpush
