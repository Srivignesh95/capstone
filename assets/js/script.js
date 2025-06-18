document.addEventListener('DOMContentLoaded', function () {
    const rsvpedEvents = window.rsvpedEvents || [];

    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        events: window.calendarEvents || [],
        eventClick: function (info) {
            const e = info.event.extendedProps;
            const eventId = parseInt(info.event.id);
            const isRSVPed = rsvpedEvents.includes(eventId);

            const registerButton = isRSVPed
                ? `<button class="btn btn-secondary w-100" disabled>Already Registered</button>`
                : `<form method="GET" action="/capstone/registered_user/confirm_public_rsvp.php">
                        <input type="hidden" name="event_id" value="${eventId}">
                        <button type="submit" class="btn btn-primary w-100">Register Now</button>
                   </form>`;

            const modalContent = `
                <h5>${info.event.title}</h5>
                <p><strong>Date:</strong> ${info.event.start.toISOString().split('T')[0]}</p>
                <p><strong>Time:</strong> ${e.time}</p>
                <p><strong>Location:</strong> ${e.hall}</p>
                <p>${e.description}</p>
                <div class="mt-3">${registerButton}</div>
            `;

            document.getElementById('eventModalBody').innerHTML = modalContent;
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        }
    });

    calendar.render();
});
