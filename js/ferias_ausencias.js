// Ferias e Ausencias - Opera
document.addEventListener('DOMContentLoaded', function() {
    initFeriasAusenciasForm();
    initFilterButtons();
});

// Modal functions
function abrirModalPedido() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function fecharModalPedido() {
    const modal = document.getElementById('modalPedido');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Reset form
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            // Reset comprovativo status
            const comprovativoStatus = document.getElementById('comprovativo-status');
            if (comprovativoStatus) {
                comprovativoStatus.textContent = '(opcional)';
            }
            const comprovativoInput = document.getElementById('ficheiro');
            if (comprovativoInput) {
                comprovativoInput.required = false;
            }
        }
    }
}

// Form functionality
function initFeriasAusenciasForm() {
    const tipoSelect = document.getElementById("tipo");
    const comprovativoInput = document.getElementById("ficheiro");
    const comprovativoStatus = document.getElementById("comprovativo-status");

    if (!tipoSelect || !comprovativoInput || !comprovativoStatus) return;

    const obrigatorios = [
        "licenca_paternidade",
        "licenca_maternidade",
        "baixa_medica",
        "baixa_seguro",
        "casamento",
        "consulta_medica"
    ];

    function atualizarObrigatoriedade() {
        const tipoSelecionado = tipoSelect.value;

        if (obrigatorios.includes(tipoSelecionado)) {
            comprovativoInput.required = true;
            comprovativoStatus.textContent = "(obrigatório)";
            comprovativoStatus.style.color = "#dc2626";
        } else {
            comprovativoInput.required = false;
            comprovativoStatus.textContent = "(opcional)";
            comprovativoStatus.style.color = "#6b7280";
        }
    }

    tipoSelect.addEventListener("change", atualizarObrigatoriedade);
    atualizarObrigatoriedade();

    // File input styling
    if (comprovativoInput) {
        comprovativoInput.addEventListener('change', function() {
            const wrapper = this.closest('.file-input-wrapper');
            const content = wrapper.querySelector('.file-input-content span');

            if (this.files.length > 0) {
                content.textContent = `Ficheiro selecionado: ${this.files[0].name}`;
                wrapper.style.borderColor = '#10b981';
                wrapper.style.backgroundColor = '#ecfdf5';
            } else {
                content.textContent = 'Clique para selecionar ficheiro';
                wrapper.style.borderColor = '#d1d5db';
                wrapper.style.backgroundColor = '';
            }
        });
    }
}

// Filter functionality
function initFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const pedidoCards = document.querySelectorAll('.pedido-card');

    if (!filterButtons.length || !pedidoCards.length) return;

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;

            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            // Filter cards
            pedidoCards.forEach(card => {
                const estado = card.dataset.estado;

                if (filter === 'all' || estado === filter) {
                    card.style.display = 'block';
                    card.classList.remove('hidden');
                } else {
                    card.style.display = 'none';
                    card.classList.add('hidden');
                }
            });
        });
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('modalPedido');
    if (modal && e.target === modal) {
        fecharModalPedido();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModalPedido();
    }
});

// Make functions global so they can be called from HTML onclick attributes
window.abrirModalPedido = abrirModalPedido;
window.fecharModalPedido = fecharModalPedido;

