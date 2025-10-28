// router.js

export const Paths = {
    INICIO: '',
    HORARIOS: 'horarios',
    APROVACAO_HORARIOS: 'aprov_horarios',
    MAPAS_HORARIOS: 'mapas_horarios',
    PEDIDOS_FERIAS: 'pedidos_ferias',
    APROVACAO_FERIAS: 'aprov_ferias',
    CONSULTA_PEDIDOS: 'consulta_pedidos',
    LISTA_INTERMEDIOS: 'lista_intermedios',
    CONTROLO_COLABS: 'controlo_colabs',
    PEDIDOS_HORAS_EXTRAS: 'pedidos_horas_extras',
    APROVACAO_HORAS_EXTRAS: 'aprov_horas_extras',
    MARCACAO_DIRETA: 'marcacao_direta',
    GESTAO_FICHAS: 'gestao_fichas',
    FINANCEIRA: 'financeira',
    FICHA_COLLAB: 'ficha_collab',
};

export const Routes = {
    [Paths.INICIO]: '/page/modules/inicio.html',
    [Paths.HORARIOS]: '/page/modules/horarios.html',
    [Paths.APROVACAO_HORARIOS]: '/page/modules/aprovacao_horarios.php',
    [Paths.MAPAS_HORARIOS]: '/page/modules/mapas_horarios.php',
    [Paths.PEDIDOS_FERIAS]: '/page/modules/pedidos_ferias.php',
    [Paths.APROVACAO_FERIAS]: '/page/modules/aprovacao_ferias.php',
    [Paths.CONSULTA_PEDIDOS]: '/page/modules/consulta_pedidos.html',
    [Paths.LISTA_INTERMEDIOS]: '/page/modules/lista_intermedios.php',
    [Paths.CONTROLO_COLABS]: '/page/modules/controlo_colabs.php',
    [Paths.PEDIDOS_HORAS_EXTRAS]: '/page/modules/pedidos_horas_extras.php',
    [Paths.APROVACAO_HORAS_EXTRAS]: '/page/modules/aprovacao_horas_extras.php',
    [Paths.MARCACAO_DIRETA]: '/page/modules/marcacao_direta.php',
    [Paths.GESTAO_FICHAS]: '/page/modules/gestao_fichas.html',
    [Paths.FINANCEIRA]: '/page/modules/financeira.html',
    [Paths.FICHA_COLLAB]: '/page/modules/ficha_collab.html'
};

const container = document.getElementById('main-content');

async function render() {
    if (!container) return;

    // 1. get the current route from the hash, e.g. "#horarios" -> "horarios"
    const raw = window.location.hash.replace(/^#/, '').trim();

    // 2. normalize: if hash is empty, use Paths.INICIO (which is '')
    //    then pick the file; if route doesn't exist, fall back to INICIO file
    const routeKey = raw === '' ? Paths.INICIO : raw;
    const file = Routes[routeKey] || Routes[Paths.INICIO];

    container.innerHTML = `<p class="muted" style="padding:1rem;">A carregar…</p>`;

    // 4. fetch and inject
    try {
        const res = await fetch(file, { credentials: 'same-origin' });
        if (!res.ok) throw new Error(`HTTP ${res.status} for ${file}`);
        container.innerHTML = await res.text();
    } catch (err) {
        console.error('Falha ao carregar vista:', err);
        container.innerHTML = `
            <div style="padding:1rem;">
                <p class="error">Não foi possível carregar esta secção.</p>
            </div>`;
    }
}


window.addEventListener('hashchange', render);
document.addEventListener('DOMContentLoaded', render);
