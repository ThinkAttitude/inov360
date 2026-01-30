import {openDropdown as openDropdownPrimitive} from '../shared/ui/dropdown/dropdown.js'

/**
 * Creates an overlay controller bound to an optional lifecycle signal.
 * @param {AbortSignal} [signal]
 * @returns {{openDropdown:(anchorEl:HTMLElement, contentEl:HTMLElement, opts?:{offset?:number,className?:string})=>any, closeActive:()=>void}}
 */
export function createOverlays(signal) {
    let active = null

    const closeActive = () => {
        if (!active) return
        active.close()
        active = null
    }

    const openDropdown = (anchorEl, contentEl, opts = {}) => {
        closeActive()
        const dd = openDropdownPrimitive(anchorEl, contentEl, {...opts, signal})
        const baseClose = dd.close
        dd.close = () => {
            baseClose()
            if (active === dd) active = null
        }
        active = dd
        return dd
    }

    if (signal) signal.addEventListener('abort', closeActive, {once: true})

    return {openDropdown, closeActive}
}

export const overlays = createOverlays()
