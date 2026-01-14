export const HORARIOS_WINDOW = Object.freeze({
    startPrevMonthDay: 25,
    endCurrMonthDay: 25,
});

export function getInitialOpMonthDate(now = new Date()) {
    const d = now;
    const shift = d.getDate() >= HORARIOS_WINDOW.startPrevMonthDay ? 1 : 0;
    return new Date(d.getFullYear(), d.getMonth() + shift, 1);
}

export function computeCalRange(currentDate) {
    const anchor = getInitialOpMonthDate(currentDate);
    const y = anchor.getFullYear();
    const m = anchor.getMonth();
    return {
        start: new Date(y, m - 1, HORARIOS_WINDOW.startPrevMonthDay),
        end: new Date(y, m, HORARIOS_WINDOW.endCurrMonthDay),
    };
}

export function navigateOpMonth(calendar, direction, viewId = 'opMonth') {
    if (!calendar) return;

    if (calendar.view.type !== viewId) {
        if (direction === 'left') calendar.prev();
        else calendar.next();
        return;
    }

    const base = getInitialOpMonthDate(calendar.getDate());
    const delta = direction === 'left' ? -1 : 1;
    calendar.gotoDate(new Date(base.getFullYear(), base.getMonth() + delta, 1));
}
