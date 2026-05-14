import {updatePassword} from "../../app/api.js";
import "./styles.css";

export function mountAlterarPassword() {
    const form = document.getElementById("form-alterar-password");
    if (!form) return null;

    const oldEl = form.querySelector("#apw-old");
    const newEl = form.querySelector("#apw-new");
    const confirmEl = form.querySelector("#apw-confirm");
    const feedback = form.querySelector("#apw-feedback");
    const submitBtn = form.querySelector("#apw-submit");
    const resetBtn = form.querySelector("#apw-reset");

    const ctrl = new AbortController();
    const {signal} = ctrl;

    const setFeedback = (message, kind = "") => {
        feedback.textContent = message || "";
        feedback.classList.remove("is-error", "is-success");
        if (kind) feedback.classList.add(`is-${kind}`);
    };

    // toggles mostrar/ocultar palavra-passe
    form.querySelectorAll(".apw-toggle-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            const targetId = btn.dataset.target;
            const input = form.querySelector(`#${targetId}`);
            if (!input) return;

            const showing = input.type === "text";
            input.type = showing ? "password" : "text";
            btn.setAttribute("aria-pressed", String(!showing));
            btn.setAttribute("aria-label", showing ? "Mostrar palavra-passe" : "Ocultar palavra-passe");

            const eye = btn.querySelector(".icon-eye");
            const eyeOff = btn.querySelector(".icon-eye-off");
            if (eye && eyeOff) {
                eye.style.display = showing ? "" : "none";
                eyeOff.style.display = showing ? "none" : "";
            }
        }, {signal});
    });

    resetBtn?.addEventListener("click", () => {
        setFeedback("");
    }, {signal});

    form.addEventListener("submit", async (ev) => {
        ev.preventDefault();
        setFeedback("");

        const old_password = oldEl.value;
        const new_password = newEl.value;
        const confirm_password = confirmEl.value;

        if (!old_password || !new_password || !confirm_password) {
            setFeedback("Todos os campos são obrigatórios.", "error");
            return;
        }

        if (new_password !== confirm_password) {
            setFeedback("A nova palavra-passe e a confirmação não coincidem.", "error");
            return;
        }

        if (new_password === old_password) {
            setFeedback("A nova palavra-passe deve ser diferente da atual.", "error");
            return;
        }

        submitBtn.disabled = true;
        const originalLabel = submitBtn.textContent;
        submitBtn.textContent = "A guardar...";

        try {
            const res = await updatePassword({old_password, new_password, confirm_password});
            if (res?.success) {
                setFeedback(res.message || "Palavra-passe atualizada com sucesso.", "success");
                form.reset();
            } else {
                setFeedback(res?.message || "Não foi possível atualizar a palavra-passe.", "error");
            }
        } catch (err) {
            setFeedback(err?.message || "Erro ao comunicar com o servidor.", "error");
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalLabel;
        }
    }, {signal});

    return () => ctrl.abort();
}
