export const DAY_KIND = Object.freeze({
    TRABALHO: 'trabalho',
    FERIAS: 'ferias',
    AUSENCIA: 'ausencia',
})

/**
 * @param {number} n
 * @returns {string}
 */
export function pad2(n) {
    return String(n).padStart(2, '0')
}

/**
 * Formats a Date into a YYYY-MM-DD string (e.g. "2026-02-06").
 * @param {Date} date
 * @returns {string}
 */
export function ymd(date) {
    return `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`
}

/**
 * Formats an exclusive end Date into an inclusive YYYY-MM-DD by subtracting 1ms (e.g. "2026-02-24").
 * @param {Date} exclusiveEnd
 * @returns {string}
 */
export function toInclusiveYmd(exclusiveEnd) {
    return ymd(new Date(exclusiveEnd.getTime() - 1))
}

/**
 * @param {Date} d
 * @param {number} n
 * @returns {Date}
 */
export function addDays(d, n) {
    return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n)
}

/**
 * @param {Date} date
 * @returns {{start: Date, end: Date}}
 */
export function weekRangeFrom(date) {
    const d = new Date(date.getFullYear(), date.getMonth(), date.getDate())
    const dow = d.getDay()
    const back = (dow + 6) % 7
    const start = addDays(d, -back)
    const end = addDays(start, 6)
    return {start, end}
}

/**
 * @param {Date} date
 * @param {(d: Date) => {start: Date, end: Date}} computeCalRange
 * @returns {{startStr: string, endStr: string}}
 */
export function periodRangeFrom(date, computeCalRange) {
    const r = computeCalRange(date)
    return {startStr: ymd(r.start), endStr: toInclusiveYmd(r.end)}
}

/**
 * @param {string} preset
 * @param {string} baseDateStr
 * @param {HTMLFormElement} form
 * @param {(d: Date) => {start: Date, end: Date}} computeCalRange
 * @returns {{startStr: string, endStr: string}}
 */
export function presetRange(preset, baseDateStr, form, computeCalRange) {
    const base = new Date(`${baseDateStr}T00:00:00`)
    if (preset === 'day') return {startStr: baseDateStr, endStr: baseDateStr}
    if (preset === 'week') {
        const r = weekRangeFrom(base)
        return {startStr: ymd(r.start), endStr: ymd(r.end)}
    }
    if (preset === 'period') return periodRangeFrom(base, computeCalRange)
    return {startStr: form.elements.rangeStart.value || baseDateStr, endStr: form.elements.rangeEnd.value || baseDateStr}
}

/**
 * @param {any} leave
 * @returns {string}
 */
export function classifyLeave(leave) {
    const raw = String(leave?.tipo || leave?.type || leave?.label || leave?.titulo || leave?.title || '').toLowerCase()
    if (raw.includes('féri') || raw.includes('feri') || raw.includes('vac')) return DAY_KIND.FERIAS
    return DAY_KIND.AUSENCIA
}

/**
 * @param {Array<any>} days
 * @returns {Map<string, any>}
 */
export function indexDaysByDate(days) {
    const map = new Map()
    for (let i = 0; i < days.length; i++) {
        const d = days[i]
        if (d && d.date) map.set(d.date, d)
    }
    return map
}

/**
 * Formats minutes into a compact time label: "8h" or "8h30".
 * @param {number} mins
 * @returns {string}
 */
export function formatWorkTime(mins) {
    if (mins <= 0) return ''
    const h = Math.floor(mins / 60)
    const r = mins % 60
    return r === 0 ? `${h}h` : `${h}h${pad2(r)}`
}

/**
 * Formats kilometers into a compact distance label: "12km" or "12.5km".
 * @param {number} km
 * @returns {string}
 */
export function formatKm(km) {
    if (km <= 0) return ''
    const r = Math.round(km * 10) / 10
    return `${String(r).replace(/\.0$/, '')}km`
}

/**
 * @param {Array<any>} days
 * @param {string} windowStartStr
 * @param {string} windowEndStr
 * @returns {Array<any>}
 */
export function buildBackgroundEvents(days, windowStartStr, windowEndStr) {
    const events = []

    for (let i = 0; i < days.length; i++) {
        const day = days[i]
        if (!day || !day.date) continue

        const dateStr = day.date
        if (dateStr < windowStartStr || dateStr >= windowEndStr) continue

        const leaves = Array.isArray(day.leaves) ? day.leaves : []
        const hasLeave = leaves.length > 0

        const workMin = day.workMin || 0
        const hasWork = !hasLeave && workMin > 0

        let kind = null
        if (hasLeave) kind = classifyLeave(leaves[0])
        else if (hasWork) kind = DAY_KIND.TRABALHO

        if (!kind) continue

        const statusClass = day.status === 'approved' ? 'is-approved' : 'is-draft'

        events.push({
            start: dateStr,
            allDay: true,
            display: 'background',
            classNames: ['legend-dot', kind, statusClass],
        })
    }

    return events
}
