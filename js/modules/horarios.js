function loadLib() {
    return new Promise((resolve, reject) => {
        if (window.FullCalendar) {
            resolve();
            return;
        }

        // inject FullCalendar CSS if not already present
        if (!document.querySelector('link[data-fullcalendar-css]')) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css';
            css.setAttribute('data-fullcalendar-css', 'true');
            document.head.appendChild(css);
        }

        // inject FullCalendar JS
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js';
        script.onload = () => resolve();
        script.onerror = reject;
        document.body.appendChild(script);
    });
}

export async function mountCalendar() {
    await loadLib();
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia'
        },
        height: 'auto',

        // Ask backend for events for the current visible month
        events: function(fetchInfo, success, fail) {
            // FullCalendar gives us fetchInfo.start (Date of start-of-range)
            const start = fetchInfo.start;
            const year = start.getFullYear();
            const month = String(start.getMonth() + 1).padStart(2, '0');
            const monthKey = `${year}-${month}`;

            // TODO: adjust the API param to search by date range and not just month
            fetch(`../../api/calendar/get_month.php?month=${encodeURIComponent(monthKey)}`, {
                credentials: 'same-origin'
            })
                .then(r => r.json())
                .then(data => {
                    // expecting { ok: true, days: [ { date: "YYYY-MM-DD", leaves: [...] }, ... ] }
                    if (!data || data.ok !== true || !Array.isArray(data.days)) {
                        fail(new Error('Formato de resposta inesperado'));
                        return;
                    }

                    const events = [];

                    data.days.forEach(day => {
                        const dateStr = day.date;
                        const leaves = Array.isArray(day.leaves) ? day.leaves : [];

                        leaves.forEach(lv => {
                            const title =
                                lv.label ||
                                lv.titulo ||
                                lv.title ||
                                lv.tipo ||
                                lv.type ||
                                'Ausência';

                            events.push({
                                title,
                                start: dateStr,
                                allDay: true
                            });
                        });
                    });

                    success(events);
                })
                .catch(err => {
                    fail(err);
                });
        },

        eventClick(info) {
            // very minimal preview for now
            alert(
                'Evento: ' +
                info.event.title +
                '\nData: ' +
                info.event.start.toLocaleDateString('pt-PT')
            );
        }
    });

    calendar.render();
}