import {openPopover as openPopoverPrimitive} from '../shared/ui/popover/popover.js'
import {openModal as openModalPrimitive} from '../shared/ui/modal/modal.js'

/**
 * Creates an overlay controller bound to an optional lifecycle signal.
 * @param {AbortSignal} [signal]
 * @returns {{openPopover: Function, openModal: Function, closeActive: Function}}
 */
export function createOverlays(signal) {
    let active = null

    const closeActive = () => {
        if (!active) return
        active.close()
        active = null
    }

    const openPopover = (anchorEl, contentEl, opts = {}) => {
        closeActive()
        const dd = openPopoverPrimitive(anchorEl, contentEl, {...opts, signal})
        const baseClose = dd.close
        dd.close = () => {
            baseClose()
            if (active === dd) active = null
        }
        active = dd
        return dd
    }

    const openModal = (opts = {}) => {
        closeActive()
        const m = openModalPrimitive({...opts, signal})
        const baseClose = m.close
        m.close = () => {
            baseClose()
            if (active === m) active = null
        }
        active = m
        return m
    }

    if (signal) signal.addEventListener('abort', closeActive, {once: true})

    return {openPopover, openModal, closeActive}
}

export const overlays = createOverlays()
