import {getCalendarTimeframe} from '../../api.js';

/**
 *
 * @type {{calendar: null, libPromise: null}}
 */
const horariosState = {
    calendar: null,
    libPromise: null,
};

const HORARIOS_WINDOW = {
    startPrevMonthDay: 25,
    endCurrMonthDay: 25,
};

const DAY_KIND = Object.freeze({
    TRABALHO: 'trabalho',
    FERIAS: 'ferias',
    AUSENCIA: 'ausencia',
});


function pad2(n) {
    return String(n).padStart(2, '0');
}

function ymd(date) {
    return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;
}

function toInclusiveYmd(exclusiveEnd) {
    return ymd(new Date(exclusiveEnd.getTime() - 1));
}

function computeCalRange(currentDate) {
    const y = currentDate.getFullYear();
    const m = currentDate.getMonth();
    return {
        start: new Date(y, m - 1, HORARIOS_WINDOW.startPrevMonthDay),
        end: new Date(y, m, HORARIOS_WINDOW.endCurrMonthDay),
    };
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
            existing.addEventListener('load', () => resolve(), {once: true});
            existing.addEventListener('error', reject, {once: true});
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

async function fetchDays(fetchInfo) {
    const from = ymd(fetchInfo.start);
    const to = toInclusiveYmd(fetchInfo.end);
    const res = await getCalendarTimeframe(from, to);
    return extractDays(res);
}

// TODO: improve leave classification and check standardization
function classifyLeave(leave) {
    const raw = String(
        leave?.tipo || leave?.type || leave?.label || leave?.titulo || leave?.title || ''
    ).toLowerCase();

    if (raw.includes('féri') || raw.includes('feri') || raw.includes('vac')) return DAY_KIND.FERIAS;
    return DAY_KIND.AUSENCIA;
}

function buildBackgroundEvents(days, windowStartStr, windowEndStr) {
    const events = [];

    for (let i = 0; i < days.length; i++) {
        const day = days[i];
        if (!day || !day.date) continue;

        const dateStr = day.date;
        if (dateStr < windowStartStr || dateStr >= windowEndStr) continue;

        const leaves = Array.isArray(day.leaves) ? day.leaves : [];
        const hasLeave = leaves.length > 0;

        const workMin = day.workMin;
        const hasWork = !hasLeave && workMin > 0;

        let kind = null;

        if (hasLeave) kind = classifyLeave(leaves[0]);
        else if (hasWork) kind = DAY_KIND.TRABALHO;

        if (!kind) continue;

        events.push({
            start: dateStr,
            allDay: true,
            display: 'background',
            classNames: ['legend-dot', kind],
        });
    }

    return events;
}

const dayCellClassNames = (arg) => {
    const inWindow = arg.date >= arg.view.currentStart && arg.date < arg.view.currentEnd;
    return inWindow ? [] : ['horarios-outside'];
}

export async function mountCalendar() {
    await ensureFullCalendar();

    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;
    if (horariosState.calendar) return;

    horariosState.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'opMonth',
        locale: 'pt',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'opMonth,timeGridWeek,timeGridDay',
        },
        views: {
            opMonth: {
                type: 'dayGrid',
                buttonText: 'Mês',
                visibleRange: computeCalRange,
            },
        },
        height: 'auto',
        dayCellClassNames,
        events: async (fetchInfo, success, fail) => {
            try {
                const days = await fetchDays(fetchInfo);

                const view = horariosState.calendar.view;
                const windowStartStr = ymd(view.currentStart);
                const windowEndStr = ymd(view.currentEnd);

                success(buildBackgroundEvents(days, windowStartStr, windowEndStr));
            } catch (err) {
                fail(err);
            }
        },
    });

    horariosState.calendar.render();
}

export function mountSchedule() {
    return mountCalendar();
}