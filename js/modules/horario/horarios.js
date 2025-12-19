import {getCalendarTimeframe} from '../../api.js';

const horariosState = {
    calendar: null,
    libPromise: null,
    monthCache: new Map(),
    daysByDate: new Map(),
};

const EVENT_COLORS = {
    event: '#3788d8',
    ferias: '#10b981',
    ausencia: '#f59e0b',
};

function pad2(n) {
    return String(n).padStart(2, '0');
}

function ymd(date) {
    return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;
}

function endInclusiveYmd(exclusiveEnd) {
    return ymd(new Date(exclusiveEnd.getTime() - 1));
}

function extractDays(res) {
    const days = res?.days || res?.data?.days;
    if (!Array.isArray(days)) throw new Error('Formato de resposta inesperado');
    return days;
}

function ensureFullCalendar() {
    if (window.FullCalendar) return Promise.resolve();
    if (horariosState.libPromise) return horariosState.libPromise;

    horariosState.libPromise = new Promise((resolve, reject) => {
        if (!document.querySelector('link[data-fullcalendar-css]')) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css';
            css.setAttribute('data-fullcalendar-css', 'true');
            document.head.appendChild(css);
        }

        if (window.FullCalendar) {
            resolve();
            return;
        }

        const existing = document.querySelector('script[data-fullcalendar-js]');
        if (existing) {
            existing.addEventListener('load', () => resolve(), { once: true });
            existing.addEventListener('error', reject, { once: true });
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js';
        script.setAttribute('data-fullcalendar-js', 'true');
        script.onload = () => resolve();
        script.onerror = reject;
        document.head.appendChild(script);
    });

    return horariosState.libPromise;
}

async function getRangeDaysCached(from, to) {
    const key = `${from}|${to}`;
    if (horariosState.rangeCache.has(key)) return horariosState.rangeCache.get(key);

    const res = await getCalendarTimeframe(from, to);
    const days = extractDays(res);

    horariosState.rangeCache.set(key, days);
    return days;
}

function classifyLeave(leave) {
    const raw = String(
        leave?.tipo || leave?.type || leave?.label || leave?.titulo || leave?.title || ''
    ).toLowerCase();

    if (raw.includes('féri') || raw.includes('feri') || raw.includes('vac')) return 'ferias';
    return 'ausencia';
}

function buildEvents(daysByDate, rangeStart, rangeEnd) {
    const events = [];

    for (const [dateStr, day] of daysByDate.entries()) {
        if (dateStr < rangeStart || dateStr > rangeEnd) continue;

        const leaves = Array.isArray(day?.leaves) ? day.leaves : [];
        if (leaves.length === 0) continue;

        for (const lv of leaves) {
            const kind = classifyLeave(lv);
            const title =
                lv?.label ||
                lv?.titulo ||
                lv?.title ||
                lv?.tipo ||
                lv?.type ||
                'Ausência';

            events.push({
                title: String(title),
                start: dateStr,
                allDay: true,
                classNames: ['horarios-event', kind],
                backgroundColor: EVENT_COLORS[kind] || EVENT_COLORS.event,
                borderColor: EVENT_COLORS[kind] || EVENT_COLORS.event,
            });
        }
    }

    return events;
}

async function loadRangeModel(fetchInfo) {
    const from = ymd(fetchInfo.start);
    const to = endInclusiveYmd(fetchInfo.end);

    const days = await getRangeDaysCached(from, to);

    const daysByDate = new Map();
    for (let i = 0; i < days.length; i++) {
        const d = days[i];
        if (!d || !d.date) continue;
        daysByDate.set(d.date, d);
    }

    const events = buildEvents(daysByDate, from, to);
    return { daysByDate, events };
}

function decorateDayCell(info) {
    const dateStr = ymd(info.date);
    const day = horariosState.daysByDate.get(dateStr);

    info.el.classList.remove('marked', 'ferias', 'today');
    info.el.dataset.date = dateStr;

    const leaves = Array.isArray(day?.leaves) ? day.leaves : [];
    const hasLeave = leaves.length > 0;
    const hasWork = !hasLeave && typeof day?.workMin === 'number' && day.workMin > 0;
    const isToday = dateStr === ymd(new Date()) && !hasLeave && !hasWork;

    if (hasLeave) info.el.classList.add('ferias');
    else if (hasWork) info.el.classList.add('marked');
    else if (isToday) info.el.classList.add('today');
}

function handleEventClick(info) {
    const date = info?.event?.start ? info.event.start.toLocaleDateString('pt-PT') : '';
    const title = info?.event?.title ? String(info.event.title) : 'Evento';
    alert(`Evento: ${title}\nData: ${date}`);
}

export async function mountCalendar() {
    await ensureFullCalendar();

    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    if (horariosState.calendar) return;

    horariosState.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
        },
        height: 'auto',
        dayCellDidMount: decorateDayCell,
        events: async (fetchInfo, success, fail) => {
            try {
                const model = await loadRangeModel(fetchInfo);
                horariosState.daysByDate = model.daysByDate;
                if (horariosState.calendar && typeof horariosState.calendar.rerenderDates === 'function') {
                    horariosState.calendar.rerenderDates();
                }
                success(model.events);
            } catch (err) {
                fail(err);
            }
        },
        eventClick: handleEventClick,
    });

    horariosState.calendar.render();
}

export function mountSchedule() {
    return mountCalendar();
}