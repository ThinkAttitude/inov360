function getStateLabel(state) {
    return state === 'approved' ? 'Aprovado' : 'Recusado';
}

export function openHistoryDetailModal({ item, openModal, esc, formatDate, formatMinutes }) {
    const m = openModal({ title: 'Detalhe do pedido' });

    m.body.innerHTML = `
        <div class="ui-modal-detail-grid">
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Colaborador</span>
                <span class="ui-modal-detail-value">${esc(item.colaborador.nome)}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Email</span>
                <span class="ui-modal-detail-value">${esc(item.colaborador.email)}</span>
            </div>
            ${item.colaborador.empresa ? `
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Empresa</span>
                <span class="ui-modal-detail-value">${esc(item.colaborador.empresa)}</span>
            </div>` : ''}
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Dia</span>
                <span class="ui-modal-detail-value">${formatDate(item.dia)}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Horário</span>
                <span class="ui-modal-detail-value">${esc(item.hora_inicio)} — ${esc(item.hora_fim)} (${formatMinutes(item.req_minutos)})</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Estado</span>
                <span class="ui-modal-detail-value"><span class="he-badge he-badge--${item.estado}">${getStateLabel(item.estado)}</span></span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Decidido por</span>
                <span class="ui-modal-detail-value">${item.decidido_por ? esc(item.decidido_por.nome) : '-'}</span>
            </div>
            <div class="ui-modal-detail-field">
                <span class="ui-modal-detail-label">Data da decisão</span>
                <span class="ui-modal-detail-value">${item.decidido_em ? esc(item.decidido_em) : '-'}</span>
            </div>
            ${item.justificacao ? `
            <div class="ui-modal-detail-field full-width">
                <span class="ui-modal-detail-label">Justificação</span>
                <span class="ui-modal-detail-value">${esc(item.justificacao)}</span>
            </div>` : ''}
            ${item.comentario ? `
            <div class="ui-modal-detail-field full-width">
                <span class="ui-modal-detail-label">Comentário</span>
                <span class="ui-modal-detail-value">${esc(item.comentario)}</span>
            </div>` : ''}
        </div>
    `;

    m.footer.innerHTML = `<button type="button" class="btn-secondary he-modal-close-btn">Fechar</button>`;
    m.footer.querySelector('.he-modal-close-btn')?.addEventListener('click', m.close);
}