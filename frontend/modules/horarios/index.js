import {createEventBatch, createEventByDay, getCalendarTimeframe} from '../../app/api.js'
import {createOverlays} from '../../app/overlays.js'
import {renderHorariosCalendar} from './horarios.js'
import {buildWorkForm} from './horarios_form.js'
import {ymd, toInclusiveYmd} from './horarios_utils.js'

import './styles.css'
import './popover-styles.css'

/**
 * CalendarDay entry.
 * @typedef {Object} CalendarDay
 * @property {string} date
 * @property {number} [workMin]
 * @property {number} [km]
 * @property {Array<any>} [leaves]
 */

/**
 * Horários view state (per mounted instance).
 * @typedef {Object} HorariosViewState
 * @property {any|null} calendar
 * @property {AbortController|null} ctrl
 * @property {Map<string, CalendarDay>} daysByDate
 */

/**
 * Horários module state.
 * @typedef {Object} HorariosState
 * @property {HorariosViewState} view
 */

/** @type {HorariosState} */
const horariosState = {
    view: {
        calendar: null,
        ctrl: null,
        daysByDate: new Map(),
    },
}

let fcPromise = null

export function loadFullCalendar() {
    if (fcPromise) return fcPromise

    fcPromise = Promise.all([
        import('@fullcalendar/core'),
        import('@fullcalendar/daygrid'),
        import('@fullcalendar/timegrid'),
        import('@fullcalendar/interaction'),
        import('@fullcalendar/core/locales/pt'),
    ]).then(([core, dayGrid, timeGrid, interaction, ptLocale]) => ({
        Calendar: core.Calendar,
        plugins: [dayGrid.default, timeGrid.default, interaction.default],
        locale: ptLocale.default,
    }))

    return fcPromise
}

async function fetchDays(fetchInfo) {
    const from = ymd(fetchInfo.start)
    const to = toInclusiveYmd(fetchInfo.end)
    const res = await getCalendarTimeframe(from, to)
    return res.days
}

function destroyCalendar() {
    const v = horariosState.view

    if (v.ctrl) v.ctrl.abort()
    v.ctrl = null

    if (v.calendar) v.calendar.destroy()
    v.calendar = null

    v.daysByDate.clear()
}

export async function mountCalendar() {
    const calRoot = document.getElementById('calendar')
    if (!calRoot) return destroyCalendar

    const v = horariosState.view

    if (v.calendar) {
        const sameEl = v.calendar.el === calRoot
        const inDom = document.contains(v.calendar.el)
        if (sameEl && inDom) return destroyCalendar
        destroyCalendar()
    }

    const ctrl = new AbortController()
    v.ctrl = ctrl

    const overlays = createOverlays(ctrl.signal)
    const fc = await loadFullCalendar()

    if (v.ctrl !== ctrl || ctrl.signal.aborted) return destroyCalendar
    if (!document.contains(calRoot)) return destroyCalendar

    const onDateClick = (info) => {
        if (ctrl.signal.aborted || horariosState.view.calendar !== v.calendar) return
        if (!info.dayEl) return

        const form = buildWorkForm({
            dateStr: info.dateStr,
            onSubmit: async ({startStr, endStr, workMin, km}) => {
                if (startStr === endStr) await createEventByDay(startStr, {workMin, km, overwrite: true})
                else await createEventBatch(startStr, endStr, {workMin, km, overwrite: true})
            },
            onDone: () => overlays.closeActive(),
        })

        overlays.openPopover(info.dayEl, form, {className: 'horarios-popover'})
    }

    v.calendar = renderHorariosCalendar(calRoot, fc, horariosState.view, {
        signal: ctrl.signal,
        fetchDays,
        onDateClick,
    })

    return destroyCalendar
}

export function mountSchedule() {
    mountCalendar()
    return destroyCalendar
}
