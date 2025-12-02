<!-- resources/views/livewire/calendar-task-view.blade.php (FullCalendar + Drag & Drop) -->
<div wire:ignore>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>

    <div id="leanderCalendar"></div>

    <script>
        document.addEventListener('livewire:load', function () {
            const calendarEl = document.getElementById('leanderCalendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                editable: true,              // permite arrastar
                droppable: true,
                selectable: true,
                eventDurationEditable: true,
                eventStartEditable: true,
                eventDrop: function(info) {
                    Livewire.dispatch('taskMoved', { id: info.event.id, date: info.event.startStr });
                },
                events: @json($events),
            });

            calendar.render();

            Livewire.on('refreshCalendar', () => {
                calendar.removeAllEvents();
                calendar.addEventSource(@json($events));
            });
        });
    </script>
</div>
