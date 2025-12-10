export function initEditToggle(button, options) {
    if (!button) return { setState: function () {} };

    const render = options.render;
    const onSave = options.onSave || (async function () { return true; });
    const labelEl = button.querySelector('.ficha-btn-label');

    function setLabel(text) {
        if (labelEl) labelEl.textContent = text;
        else button.textContent = text;
    }

    function clone(obj) {
        return JSON.parse(JSON.stringify(obj || {}));
    }

    let base = null;
    let current = null;
    let mode = 'view';
    let secondary = null;

    function dirty() {
        return JSON.stringify(current) !== JSON.stringify(base);
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
            if (!base) return;
            current = clone(base);
            mode = 'view';
            renderMode();
            updatePrimaryBtn();
        });
        return secondary;
    }

    function updatePrimaryBtn() {
        button.classList.remove('btn-variant-green', 'btn-variant-red');
        const isDirty = dirty();

        if (secondary && (mode !== 'edit' || !isDirty)) {
            secondary.remove();
            secondary = null;
        }

        switch (mode) {
            case 'view':
                setLabel('Editar');
                button.classList.add('btn-variant-green');
                break;
            case 'edit':
                if (!isDirty) {
                    setLabel('Cancelar');
                    button.classList.add('btn-variant-red');
                } else {
                    setLabel('Guardar');
                    renderSecondaryBtn();
                }
                break;
            default:
                setLabel('Editar');
                button.classList.add('btn-variant-green');
        }
    }

    function handleChange(nextState) {
        current = nextState;
        updatePrimaryBtn();
    }

    function renderMode() {
        if (!render) return;
        render(mode, current, handleChange);
    }

    button.addEventListener('click', async function (e) {
        e.preventDefault();
        if (mode === 'view') {
            mode = 'edit';
            renderMode();
            updatePrimaryBtn();
            return;
        }
        if (!dirty()) {
            current = clone(base);
            mode = 'view';
            renderMode();
            updatePrimaryBtn();
            return;
        }
        const ok = await onSave(current);
        if (!ok) return;
        base = clone(current);
        mode = 'view';
        renderMode();
        updatePrimaryBtn();
    });

    function setState(state) {
        base = clone(state);
        current = clone(state);
        mode = 'view';
        renderMode();
        updatePrimaryBtn();
    }

    return { setState };
}
