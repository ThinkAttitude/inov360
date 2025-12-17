function loadLib() {
    return new Promise((resolve, reject) => {
        if (window.FullCalendar) {
            resolve();
            return;
        }

        // FullCalendar CSS
        if (!document.querySelector('link[data-fullcalendar-css]')) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css';
            css.setAttribute('data-fullcalendar-css', 'true');
            document.head.appendChild(css);
        }

        // FullCalendar JS
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js';
        script.onload = () => resolve();
        script.onerror = reject;
        document.body.appendChild(script);
    });
}

export function markDays(days) {
    if (!Array.isArray(days)) return;

    const byDate = new Map();
    days.forEach(d => {
        if (!d || !d.date) return;
        byDate.set(d.date, d);
    });

    const todayStr = new Date().toISOString().slice(0, 10);

    document.querySelectorAll('.day-cell[data-date]').forEach(cell => {
        const date = cell.dataset.date;
        const info = byDate.get(date);

        cell.classList.remove('today', 'marked', 'ferias');
        cell.onclick = null;
        cell.style.cursor = '';

        if (!info) return;

        const hasLeave = Array.isArray(info.leaves) && info.leaves.length > 0;
        const hasWork = !hasLeave && typeof info.workMin === 'number' && info.workMin > 0;

        if (date === todayStr && !hasLeave && !hasWork) {
            cell.classList.add('today');
        }

        if (hasLeave) {
            cell.classList.add('ferias');
            cell.style.cursor = 'default';

            let details = cell.querySelector('.day-details');
            if (!details) {
                details = document.createElement('div');
                details.className = 'day-details';
                cell.appendChild(details);
            }

            let badge = details.querySelector('.ferias-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'ferias-badge';
                details.appendChild(badge);
            }
            badge.textContent = 'FÉRIAS';
        } else if (hasWork) {
            cell.classList.add('marked');
            cell.style.cursor = 'pointer';
            cell.onclick = () => {
                if (typeof window.openDayModal === 'function') {
                    window.openDayModal(date);
                }
            };
        }
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

        events: function(fetchInfo, success, fail) {
            const start = fetchInfo.start;
            const year = start.getFullYear();
            const month = String(new Date().getMonth() + 1).padStart(2, '0');
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
                    markDays(data.days);
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