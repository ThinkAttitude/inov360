import {formatKm, formatWorkTime} from './horarios_utils.js'

function detailRow(label, value) {
    const row = document.createElement('div')
    row.className = 'horarios-detail-row'

    const labelEl = document.createElement('span')
    labelEl.className = 'horarios-detail-label'
    labelEl.textContent = label

    const valueEl = document.createElement('span')
    valueEl.className = 'horarios-detail-value'
    valueEl.textContent = value || '-'

    row.append(labelEl, valueEl)
    return row
}

function formatLeaves(leaves) {
    if (!Array.isArray(leaves) || !leaves.length) return ''
    return leaves.map(leave => leave.title || 'Ausência').join(', ')
}

export function buildDayDetails({dateStr, day}) {
    const root = document.createElement('div')
    root.className = 'horarios-detail-popover'

    const title = document.createElement('h3')
    title.className = 'horarios-detail-title'
    title.textContent = 'Detalhes do dia'

    const subtitle = document.createElement('p')
    subtitle.className = 'horarios-detail-subtitle'
    subtitle.textContent = dateStr

    const leaves = Array.isArray(day?.leaves) ? day.leaves : []
    const status = day?.status === 'approved' ? 'Aprovado' : 'Rascunho'

    root.append(
        title,
        subtitle,
        detailRow('Estado', status),
        detailRow('Horas', formatWorkTime(day?.workMin || 0)),
        detailRow('Quilómetros', formatKm(day?.km || 0)),
        detailRow('Tipo', formatLeaves(leaves))
    )

    return root
}