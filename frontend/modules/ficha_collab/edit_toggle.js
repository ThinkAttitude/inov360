export function initEditToggle(button, options = {}) {
    const empty = {
        setState: function () {},
        destroy: function () {},
    };

    if (!button) return empty;

    const render = options.render;
    const onSave = options.onSave || async function () { return true; };
    const storageKey = options.storageKey || '';
    const ctrl = new AbortController();
    const labelEl = button.querySelector('.ficha-btn-label');

    let base = {};
    let current = {};
    let mode = 'view';
    let secondary = null;
    let saving = false;

    function clone(obj) {
        return JSON.parse(JSON.stringify(obj || {}));
    }

    function setLabel(text) {
        if (labelEl) labelEl.textContent = text;
        else button.textContent = text;
    }

    function dirty() {
        return JSON.stringify(current) !== JSON.stringify(base);
    }

    function readStored() {
        if (!storageKey) return null;

        try {
            const raw = sessionStorage.getItem(storageKey);
            if (!raw) return null;

            const data = JSON.parse(raw);
            if (!data || data.mode !== 'edit') return null;

            return data;
        } catch {
            sessionStorage.removeItem(storageKey);
            return null;
        }
    }

    function persist() {
        if (!storageKey) return;

        if (mode !== 'edit') {
            sessionStorage.removeItem(storageKey);
            return;
        }

        sessionStorage.setItem(storageKey, JSON.stringify({
            mode,
            current,
        }));
    }

    function removeSecondary() {
        if (!secondary) return;
        secondary.remove();
        secondary = null;
    }

    function cancelEdit() {
        current = clone(base);
        mode = 'view';
        persist();
        renderMode();
        updatePrimaryBtn();
    }

    function renderSecondaryBtn() {
        if (secondary) return secondary;

        secondary = document.createElement('button');
        secondary.type = 'button';
        secondary.className = 'btn-secondary';
        secondary.textContent = 'Cancelar';

        const parent = button.parentNode;
        if (parent) parent.insertBefore(secondary, button);

        secondary.addEventListener('click', function (e) {
            e.preventDefault();
            if (!saving) cancelEdit();
        }, {signal: ctrl.signal});

        return secondary;
    }

    function updatePrimaryBtn() {
        const isDirty = dirty();

        button.disabled = saving;
        button.classList.remove('btn-variant-green', 'btn-variant-red');

        if (secondary) secondary.disabled = saving;

        if (mode !== 'edit' || !isDirty) removeSecondary();

        if (mode === 'view') {
            setLabel('Editar');
            button.classList.add('btn-variant-green');
            return;
        }

        if (!isDirty) {
            setLabel('Cancelar');
            button.classList.add('btn-variant-red');
            return;
        }

        setLabel(saving ? 'A guardar...' : 'Guardar');
        renderSecondaryBtn();
    }

    function handleChange(nextState) {
        current = clone(nextState);
        persist();
        updatePrimaryBtn();
    }

    function renderMode() {
        if (typeof render === 'function') {
            render(mode, current, handleChange);
        }
    }

    async function handlePrimaryClick(e) {
        e.preventDefault();

        if (saving) return;

        if (mode === 'view') {
            current = clone(base);
            mode = 'edit';
            persist();
            renderMode();
            updatePrimaryBtn();
            return;
        }

        if (!dirty()) {
            cancelEdit();
            return;
        }

        saving = true;
        updatePrimaryBtn();

        try {
            const ok = await onSave(current);
            if (!ok) return;

            current = clone(base);
            mode = 'view';
            persist();
            renderMode();
        } finally {
            saving = false;
            updatePrimaryBtn();
        }
    }

    button.addEventListener('click', handlePrimaryClick, {signal: ctrl.signal});

    function setState(state) {
        base = clone(state);

        const stored = readStored();
        if (stored) {
            current = clone(stored.current);
            mode = 'edit';
        } else {
            current = clone(base);
            mode = 'view';
        }

        renderMode();
        updatePrimaryBtn();
    }

    function destroy() {
        ctrl.abort();
        removeSecondary();
    }

    return {
        setState,
        destroy,
    };
}