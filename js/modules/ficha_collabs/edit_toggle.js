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

    function dirty() {
        return JSON.stringify(current) !== JSON.stringify(base);
    }

    function updateButton() {
            button.classList.remove('btn-variant-green', 'btn-variant-red');

            switch (mode) {
                case 'view':
                    setLabel('Editar');
                    button.classList.add('btn-variant-green');
                    break;
                case 'edit':
                    if (!dirty()) {
                        setLabel('Cancelar');
                        button.classList.add('btn-variant-red');
                    } else {
                        setLabel('Guardar');
                    }
                    break;
                default:
                    break;
            }
        }

    function handleChange(nextState) {
        current = nextState;
        updateButton();
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
            updateButton();
            return;
        }
        if (!dirty()) {
            current = clone(base);
            mode = 'view';
            renderMode();
            updateButton();
            return;
        }
        const ok = await onSave(current);
        if (!ok) return;
        base = clone(current);
        mode = 'view';
        renderMode();
        updateButton();
    });

    function setState(state) {
        base = clone(state);
        current = clone(state);
        mode = 'view';
        renderMode();
        updateButton();
    }

    return { setState };
}
