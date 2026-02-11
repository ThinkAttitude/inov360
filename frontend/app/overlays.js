import {openPopover as openPopoverPrimitive} from '../shared/ui/popover/popover.js'
import {openModal as openModalPrimitive} from '../shared/ui/modal/modal.js'

/**
 * Creates an overlay controller bound to an optional lifecycle signal.
 * @param {AbortSignal} [signal]
 * @returns {{openPopover:(anchorEl:HTMLElement, contentEl:HTMLElement, opts?:{offset?:number,className?:string})=>any, closeActive:()=>void}}
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

    const openModal = (contentEl, opts = {}) => {
        closeActive()
        const dd = openModalPrimitive(contentEl, { ...opts, signal })
        const baseClose = dd.close
        dd.close = () => {
            baseClose()
            if (active === dd) active = null
        }
        active = dd
        return dd
    }

    if (signal) signal.addEventListener('abort', closeActive, {once: true})

    return {openPopover, openModal, closeActive}
}