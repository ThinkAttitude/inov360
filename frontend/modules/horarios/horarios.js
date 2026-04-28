import {computeCalRange, getInitialOpMonthDate, navigateOpMonth} from './horarios_window.js'
import {DAY_KIND, buildBackgroundEvents, indexDaysByDate, formatWorkTime, formatKm, ymd} from './horarios_utils.js'

function dayCellClassNames(arg) {
    const inWindow = arg.date >= arg.view.currentStart && arg.date < arg.view.currentEnd
    return inWindow ? [] : ['horarios-outside']
}

function renderBadges(cal, daysByDate) {
    if (!cal || cal.view.type !== 'opMonth') return
    const root = cal.el
    if (!root) return

    root.querySelectorAll('.horarios-work-badge, .horarios-km-badge').forEach(el => el.remove())

    const startStr = ymd(cal.view.currentStart)
    const endStr = ymd(cal.view.currentEnd)

    root.querySelectorAll('.fc-daygrid-day[data-date]').forEach(cell => {
        const dateStr = cell.dataset.date
        if (!dateStr || dateStr < startStr || dateStr >= endStr) return

        const day = daysByDate.get(dateStr)
        if (!day) return

        const leaves = Array.isArray(day.leaves) ? day.leaves : []
        if (leaves.length) return

        const top = cell.querySelector('.fc-daygrid-day-top')
        if (!top) return

        const statusClass = day.status === 'approved' ? 'is-approved' : 'is-draft'

        const workLabel = formatWorkTime(day.workMin)
        if (workLabel) {
            const badge = document.createElement('span')
            badge.className = `horarios-work-badge legend-dot ${DAY_KIND.TRABALHO} ${statusClass}`
            badge.textContent = workLabel
            top.appendChild(badge)
        }

        const kmLabel = formatKm(day.km)
        if (kmLabel) {
            const badge = document.createElement('span')
            badge.className = `horarios-work-badge legend-dot ${DAY_KIND.TRABALHO} ${statusClass}`
            badge.textContent = kmLabel
            top.appendChild(badge)
        }
    })
}

export function renderHorariosCalendar(calRoot, fc, viewState, deps) {
    let windowStartStr = null
    let windowEndStr = null

    const initialRange = computeCalRange(getInitialOpMonthDate())
    windowStartStr = ymd(initialRange.start)
    windowEndStr = ymd(initialRange.end)

    let cal = null

    cal = new fc.Calendar(calRoot, {
        plugins: fc.plugins,
        initialView: 'opMonth',
        initialDate: getInitialOpMonthDate(),
        locale: fc.locale,
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
            opPrev: {icon: 'chevron-left', click: () => navigateOpMonth(cal, 'left', 'opMonth')},
            opNext: {icon: 'chevron-right', click: () => navigateOpMonth(cal, 'right', 'opMonth')},
        },
        height: 'auto',
        datesSet: (arg) => {
            windowStartStr = ymd(arg.view.currentStart)
            windowEndStr = ymd(arg.view.currentEnd)
            renderBadges(cal, viewState.daysByDate)
        },
        dayCellClassNames,
        dateClick: deps.onDateClick,
        events: async (fetchInfo, success, fail) => {
            try {
                if (deps.signal.aborted) return

                const days = await deps.fetchDays(fetchInfo)

                if (deps.signal.aborted) return

                viewState.daysByDate = indexDaysByDate(days)

                success(buildBackgroundEvents(days, windowStartStr, windowEndStr))

                requestAnimationFrame(() => {
                    if (!deps.signal.aborted) renderBadges(cal, viewState.daysByDate)
                })
            } catch (err) {
                if (!deps.signal.aborted) fail(err)
            }
        },
    })

    cal.render()
    return cal
}
