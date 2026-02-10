import {computeCalRange} from './horarios_window.js'
import {presetRange} from './horarios_utils.js'

export function buildWorkForm({dateStr, onSubmit, onDone}) {
    const form = document.createElement('form')
    form.className = 'horarios-day-form'

    const row = (labelText, el) => {
        const label = document.createElement('label')
        label.className = 'field-row'

        const t = document.createElement('span')
        t.className = 'field-label'
        t.textContent = labelText

        el.classList.add('field-input')

        label.appendChild(t)
        label.appendChild(el)
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

    const preset = document.createElement('select')
    preset.name = 'rangePreset'

    const opt = (v, t) => {
        const o = document.createElement('option')
        o.value = v
        o.textContent = t
        return o
    }

    preset.appendChild(opt('day', 'Apenas este dia'))
    preset.appendChild(opt('week', 'Esta semana'))
    preset.appendChild(opt('period', 'Este período'))
    preset.appendChild(opt('custom', 'Custom…'))

    const customWrap = document.createElement('div')
    customWrap.style.display = 'none'

    const rangeStart = document.createElement('input')
    rangeStart.type = 'date'
    rangeStart.name = 'rangeStart'
    rangeStart.value = dateStr

    const rangeEnd = document.createElement('input')
    rangeEnd.type = 'date'
    rangeEnd.name = 'rangeEnd'
    rangeEnd.value = dateStr

    customWrap.appendChild(row('Desde', rangeStart))
    customWrap.appendChild(row('Até', rangeEnd))

    preset.addEventListener('change', () => {
        customWrap.style.display = preset.value === 'custom' ? '' : 'none'
        if (preset.value !== 'custom') {
            rangeStart.value = dateStr
            rangeEnd.value = dateStr
        }
    })

    form.appendChild(row('Horas de trabalho', work))
    form.appendChild(row('Quilometragem', km))
    form.appendChild(row('Aplicar a', preset))
    form.appendChild(customWrap)

    form.addEventListener('submit', async (e) => {
        e.preventDefault()

        const workHours = Number(form.elements.workHours.value || 0)
        const travelKm = Number(form.elements.travelKm.value || 0)
        const workMin = Math.max(0, Math.trunc(workHours)) * 60
        const kmVal = Math.max(0, travelKm)

        const p = form.elements.rangePreset.value || 'day'
        const r = presetRange(p, dateStr, form, computeCalRange)

        await onSubmit({startStr: r.startStr, endStr: r.endStr, workMin, km: kmVal})
        onDone()
    })

    return form
}
