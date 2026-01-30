import {getCalendarTimeframe} from '../../app/api.js';
import {computeCalRange, getInitialOpMonthDate, navigateOpMonth} from './horarios_window.js';
import {createOverlays} from "../../app/overlays.js";

import './styles.css'

const DAY_KIND = Object.freeze({
    TRABALHO: 'trabalho',
    FERIAS: 'ferias',
    AUSENCIA: 'ausencia',
});

/**
 * CalendarDay entry
 * @typedef {Object} CalendarDay
 * @property {string} date
 * @property {number} [workMin]
 * @property {Array<any>} [leaves]
 */

/**
 * Horários calendar state
 * @type {{
 *   calendar: FullCalendar.Calendar|null,
 *   libPromise: Promise<void>|null,
 *   daysByDate: Map<string, CalendarDay>
 * }}
 */
const horariosState = {
    libPromise: null,
    view: {
        calendar: null,
        ctrl: null,
        daysByDate: new Map(),
    },
};

function pad2(n) {
    return String(n).padStart(2, '0');
}

function ymd(date) {
    return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;
}

function toInclusiveYmd(exclusiveEnd) {
    return ymd(new Date(exclusiveEnd.getTime() - 1));
}

function formatWorkTime(mins) {
    const m = typeof mins === 'number' ? mins : 0;
    if (m <= 0) return '';
    const h = Math.floor(m / 60);
    const r = m % 60;
    return r === 0 ? `${h}h` : `${h}h${pad2(r)}`;
}

function loadFullCalendar() {
    if (window.FullCalendar?.Internal?.globalLocales) return Promise.resolve();
    if (horariosState.libPromise) return horariosState.libPromise;

    horariosState.libPromise = new Promise((resolve, reject) => {
        const loadLocales = () => {
            if (window.FullCalendar?.Internal?.globalLocales) {
                resolve();
                return;
            }

            let s = document.querySelector('script[data-fullcalendar-locales]');
            if (s) {
                s.addEventListener('load', resolve, {once: true});
                s.addEventListener('error', reject, {once: true});
                return;
            }

            s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.8/locales-all.global.min.js';
            s.setAttribute('data-fullcalendar-locales', 'true');
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        };

        if (!document.querySelector('link[data-fullcalendar-css]')) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css';
            css.setAttribute('data-fullcalendar-css', 'true');
            document.head.appendChild(css);
        }

        if (window.FullCalendar) {
            loadLocales();
            return;
        }

        let main = document.querySelector('script[data-fullcalendar-js]');
        if (main) {
            main.addEventListener('load', loadLocales, {once: true});
            main.addEventListener('error', reject, {once: true});
            return;
        }

        main = document.createElement('script');
        main.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js';
        main.setAttribute('data-fullcalendar-js', 'true');
        main.onload = loadLocales;
        main.onerror = reject;
        document.head.appendChild(main);
    });

    return horariosState.libPromise;
}

async function fetchDays(fetchInfo) {
    const from = ymd(fetchInfo.start);
    const to = toInclusiveYmd(fetchInfo.end);
    const res = await getCalendarTimeframe(from, to);
    return res.days;
}

// TODO: improve classification logic and use API-provided standardized types
function classifyLeave(leave) {
    const raw = String(
        leave?.tipo || leave?.type || leave?.label || leave?.titulo || leave?.title || ''
    ).toLowerCase();

    if (raw.includes('féri') || raw.includes('feri') || raw.includes('vac')) return DAY_KIND.FERIAS;
    return DAY_KIND.AUSENCIA;
}

// Converts the API days array into a Map keyed by YYYY-MM-DD so
// UI rendering can do O(1) lookups per cell
function indexDaysByDate(days) {
    const map = new Map();
    for (let i = 0; i < days.length; i++) {
        const d = days[i];
        if (d && d.date) map.set(d.date, d);
    }
    return map;
}

/**
 * Builds the day details form for the schedule dropdown.
 * @returns {HTMLFormElement}
 */
function buildWorkForm() {
    const form = document.createElement('form')
    form.className = 'horarios-day-form'

    const row = (labelText, inputEl) => {
        const label = document.createElement('label')
        label.className = 'horarios-day-form-row'

        const t = document.createElement('span')
        t.className = 'field-label'
        t.textContent = labelText

        label.appendChild(t)
        label.appendChild(inputEl)
        return label
    }

    const work = document.createElement('input')
    work.type = 'number'
    work.min = '0'
    work.step = '1'
    work.inputMode = 'numeric'
    work.name = 'workHours'

    const km = document.createElement('input')
    km.type = 'number'
    km.min = '0'
    km.step = '0.1'
    km.inputMode = 'decimal'
    km.name = 'travelKm'

    form.appendChild(row('Work hours', work))
    form.appendChild(row('Travel km', km))

    return form
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

function renderWorkBadges() {
    const cal = horariosState.view.calendar;
    if (!cal || cal.view.type !== 'opMonth') return;

    const root = cal.el;
    if (!root) return;

    root.querySelectorAll('.horarios-work-badge').forEach(el => el.remove());

    const daysByDate = horariosState.view.daysByDate;
    if (!daysByDate || daysByDate.size === 0) return;

    const startStr = ymd(cal.view.currentStart);
    const endStr = ymd(cal.view.currentEnd);

    root.querySelectorAll('.fc-daygrid-day[data-date]').forEach(cell => {
        const dateStr = cell.dataset.date;
        if (!dateStr || dateStr < startStr || dateStr >= endStr) return;

        const day = daysByDate.get(dateStr);
        if (!day) return;

        const leaves = Array.isArray(day.leaves) ? day.leaves : [];
        if (leaves.length) return;

        const label = formatWorkTime(day.workMin);
        if (!label) return;

        const top = cell.querySelector('.fc-daygrid-day-top');
        if (!top) return;

        const badge = document.createElement('span');
        badge.className = `horarios-work-badge legend-dot ${DAY_KIND.TRABALHO}`;
        badge.textContent = label;
        top.appendChild(badge);
    });
}

function dayCellClassNames(arg) {
    const inWindow = arg.date >= arg.view.currentStart && arg.date < arg.view.currentEnd;
    return inWindow ? [] : ['horarios-outside'];
}

function destroyCalendar() {
    const v = horariosState.view;

    if (v.ctrl) v.ctrl.abort();
    v.ctrl = null;

    if (v.calendar) v.calendar.destroy();
    v.calendar = null;

    v.daysByDate.clear();
}


export async function mountCalendar() {
    const calRoot = document.getElementById('calendar');
    if (!calRoot) return destroyCalendar;

    const v = horariosState.view;

    if (v.calendar) {
        const sameEl = v.calendar.el === calRoot;
        const inDom = document.contains(v.calendar.el);
        if (sameEl && inDom) return destroyCalendar;
        destroyCalendar();
    }

    const ctrl = new AbortController();
    v.ctrl = ctrl;

    const overlays = createOverlays(ctrl.signal)

    await loadFullCalendar();

    if (v.ctrl !== ctrl || ctrl.signal.aborted) return destroyCalendar;
    if (!document.contains(calRoot)) return destroyCalendar;

    let cal = null;

    cal = new FullCalendar.Calendar(calRoot, {
        initialView: 'opMonth',
        initialDate: getInitialOpMonthDate(),
        locale: 'pt',
        headerToolbar: {
            left: 'opPrev,opNext today',
            center: 'title',
            right: 'opMonth,dayGridMonth,timeGridWeek,timeGridDay',
        },
        views: {
            opMonth: {
                type: 'dayGrid',
                buttonText: 'Período',
                visibleRange: computeCalRange,
            },
        },
        customButtons: {
            opPrev: {
                icon: 'chevron-left',
                click: () => navigateOpMonth(cal, 'left', 'opMonth'),
            },
            opNext: {
                icon: 'chevron-right',
                click: () => navigateOpMonth(cal, 'right', 'opMonth'),
            },
        },
        height: 'auto',
        datesSet: renderWorkBadges,
        dayCellClassNames,
        dateClick: (info) => {
            if (ctrl.signal.aborted || horariosState.view.calendar !== cal) return
            if (!info.dayEl) return
            overlays.openDropdown(info.dayEl, buildWorkForm(), {className: 'horarios-dropdown'})
        },
        events: async (fetchInfo, success, fail) => {
            try {
                if (ctrl.signal.aborted || horariosState.view.calendar !== cal) return;

                const days = await fetchDays(fetchInfo);

                if (ctrl.signal.aborted || horariosState.view.calendar !== cal) return;

                horariosState.view.daysByDate = indexDaysByDate(days);

                const view = cal.view;
                const windowStartStr = ymd(view.currentStart);
                const windowEndStr = ymd(view.currentEnd);

                success(buildBackgroundEvents(days, windowStartStr, windowEndStr));

                requestAnimationFrame(() => {
                    if (!ctrl.signal.aborted && horariosState.view.calendar === cal) renderWorkBadges();
                });
            } catch (err) {
                if (!ctrl.signal.aborted && horariosState.view.calendar === cal) fail(err);
            }
        },
    });

    v.calendar = cal;
    cal.render();

    return destroyCalendar;
}

export function mountSchedule() {
    mountCalendar();
    return destroyCalendar;
}