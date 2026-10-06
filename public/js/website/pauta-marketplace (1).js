(function () {
    'use strict';
    const box = document.getElementById('pautaMarketplaceContext');
    if (!box) return;
    const C = window.LogiGatePauta;
    const context = C.marketContext(location.search);
    if (!context.codigo) return;
    const code = document.getElementById('pautaMarketplaceCodigo');
    const description = document.getElementById('pautaMarketplaceDescricao');
    const status = document.getElementById('pautaMarketplaceEstado');
    const back = document.getElementById('pautaMarketplaceVoltar');
    const returnState = { q: context.q, page: context.page, searched: context.searched, selected: context.codigo };
    back.href = C.consultationURL(box.dataset.consultaUrl, returnState);
    code.textContent = context.codigo;
    box.hidden = false;
    description.textContent = 'A confirmar a mercadoria selecionada…';
    status.textContent = 'A pesquisa abaixo consulta o diretório geral. A experiência por código pautal ainda não está disponível.';
    // Resolve the description from the existing public tariff API, never from URL text.
    fetch(box.dataset.pautaApiBase + '/pauta/' + encodeURIComponent(context.codigo), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then(response => { if (!response.ok) throw new Error(); return response.json(); })
        .then(data => {
            const item = data.success ? C.item(data.data) : null;
            if (!item || C.normalizedCode(item.codigo) !== C.normalizedCode(context.codigo)) throw new Error();
            code.textContent = item.codigo; description.textContent = item.descricao;
            window.logigatePautaMarketplaceContext = { codigo: item.codigo, descricao: item.descricao };
            returnState.selected = item.codigo;
            back.href = C.consultationURL(box.dataset.consultaUrl, returnState);
        })
        .catch(() => {
            description.textContent = 'Não foi possível confirmar os detalhes desta mercadoria. Regresse à consulta para rever a seleção.';
        });
})();
