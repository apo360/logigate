(function () {
    'use strict';
    const C = window.LogiGatePauta;
    const $ = id => document.getElementById(id);
    const config = document.body.dataset;
    const API = config.pautaApiBase;
    const chapters = [['01', 'Animais vivos'], ['02', 'Carnes'], ['84', 'Máquinas'], ['85', 'Elétricos'], ['87', 'Veículos'], ['90', 'Instrumentos']];
    const chapterName = codigo => chapters.find(chapter => chapter[0] === codigo.slice(0, 2))?.[1] || '';
    const storageKey = 'logigate.pauta.consulta.v1';
    let state = C.readState(location.search), selected = null, detail = null, requestId = 0, detailId = 0, suggestionId = 0;
    let searchController, detailController, suggestionController, debounce, opener;
    function store() {
        const url = C.consultationURL(config.consultaUrl, state, selected?.codigo);
        history.replaceState(null, '', url);
        try { sessionStorage.setItem(storageKey, JSON.stringify({ q: state.q, page: state.page, searched: state.searched, selected: selected?.codigo || '' })); } catch {}
    }
    async function json(url, signal) {
        const response = await fetch(url, { signal, headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (!response.ok) throw new Error(response.status === 429 ? 'Muitas consultas. Aguarde um momento e tente novamente.' : 'Não foi possível consultar a pauta. Tente novamente.');
        const data = await response.json();
        if (!data.success) throw new Error('Não foi possível consultar a pauta. Tente novamente.');
        return data;
    }
    function hideSuggestions() {
        clearTimeout(debounce); suggestionId++; suggestionController?.abort();
        $('suggestions').hidden = true; $('searchInput').setAttribute('aria-expanded', 'false');
    }
    function clearSelection() {
        detailId++; detailController?.abort(); selected = null; detail = null; state.selected = '';
        if ($('detailModal').open) $('detailModal').close();
        renderGuide();
    }
    function guideHeader() {
        return '<div class="guide-header"><p class="eyebrow">O PRÓXIMO PASSO</p><div><h2>O seu guia de consulta</h2><span aria-hidden="true">↗</span></div></div>';
    }
    function renderGuide() {
        if (!selected) {
            $('guide').innerHTML = guideHeader() + '<div class="guide-body guide-empty"><span class="pill">À espera da sua seleção</span><h3>Uma consulta que continua consigo.</h3><p>Ao escolher uma mercadoria, o guia acompanha o seu código e ajuda-o a avançar.</p><ol class="guide-steps"><li><span>1</span><div><strong>Identifique a mercadoria</strong><p>Pesquise e explore o detalhe.</p></div></li><li><span>2</span><div><strong>Valide o enquadramento</strong><p>Confira taxas, unidades e requisitos.</p></div></li><li><span>3</span><div><strong>Encontre apoio</strong><p>Continue para o marketplace com contexto.</p></div></li></ol></div><div class="guide-foot">O contexto surge depois de selecionar uma mercadoria.</div>';
            return;
        }
        const simulator = new URL(config.simuladorUrl, location.origin);
        simulator.searchParams.set('codigo', selected.codigo);
        const fields = selected.requisitos
            ? '<div class="note"><strong>Requisitos da mercadoria</strong><p class="pre-line">' + C.escape(selected.requisitos) + '</p></div>'
            : '<div class="note">Os requisitos não estão disponíveis neste registo. Confirme o enquadramento com um despachante oficial.</div>';
        $('guide').innerHTML = guideHeader() + '<div class="guide-body"><div class="selected-context"><span class="tiny-label">MERCADORIA SELECIONADA</span><div class="context-title"><span class="code">' + selected.codigo + '</span><button data-deselect>Alterar</button></div><p>' + C.escape(selected.descricao) + '</p></div><ol class="guide-steps" style="margin:0 0 20px"><li class="done"><span>✓</span><div><strong>Mercadoria identificada</strong><p><button class="text-link" data-detail="' + selected.codigo + '">Rever os detalhes ↗</button></p></div></li><li><span>2</span><div><strong>Valide os custos da operação</strong><p><a class="text-link" href="' + C.escape(simulator.pathname + simulator.search) + '">Abrir o simulador existente ↗</a></p></div></li></ol>' + fields + '<h3 class="guide-section-title" style="margin-top:22px">Despachantes com experiência nesta mercadoria</h3><div class="no-history"><strong>Histórico ainda indisponível</strong>A correspondência por mercadoria ainda não está disponível. Pode explorar o marketplace levando o código e a descrição selecionados.</div><a class="secondary full guide-market-link" href="' + C.escape(C.marketplaceURL(config.marketplaceUrl, selected, state)) + '">Ver todos no marketplace →</a></div><div class="guide-foot">O código ' + selected.codigo + ' acompanha a navegação. Ao regressar, a sua consulta é mantida.</div>';
    }
    function renderChapters() {
        $('chapter-filters').innerHTML = chapters.map(([code, name]) => '<button class="chapter-chip ' + (state.q === code ? 'active' : '') + '" data-capitulo="' + code + '" aria-pressed="' + (state.q === code) + '"><b>' + code + '</b> · ' + name + '</button>').join('');
    }
    function empty() {
        $('results-heading').textContent = 'Encontre a sua mercadoria';
        $('results-count').textContent = '';
        $('results').innerHTML = '<div class="empty-card"><span class="empty-arrow" aria-hidden="true">↗</span><div class="empty-icon" aria-hidden="true">⌕</div><h3>Comece por uma consulta.</h3><p>Pesquise um código ou descreva a mercadoria. Abra um resultado para explorar os detalhes e orientar os passos seguintes.</p><div class="example-row"><span>Sugestões de pesquisa</span><button data-search="0203">0203 · Carne de suíno →</button><button data-search="máquinas">Máquinas →</button></div></div>';
        $('pagination').innerHTML = ''; $('results').setAttribute('aria-busy', 'false');
    }
    function displayResults(data) {
        const items = C.collection(data), meta = data.meta || {};
        $('results-heading').textContent = state.q ? 'Resultados para “' + state.q + '”' : 'Resultados da pauta';
        const paginated = Number.isInteger(meta.last_page) && Number.isInteger(meta.current_page);
        $('results-count').textContent = paginated && Number.isInteger(meta.total) ? meta.total + ' resultados' : items.length + ' resultados apresentados';
        $('pagination').innerHTML = '';
        if (!items.length) {
            $('results').innerHTML = '<div class="error-empty"><div class="empty-icon" style="margin:0 auto 20px" aria-hidden="true">⌕</div><h3>Nenhum resultado encontrado</h3><p>Experimente outro código, uma descrição mais curta ou um dos capítulos.</p><button class="secondary" data-clear>Limpar pesquisa</button></div>';
            return;
        }
        $('results').innerHTML = '<p class="selection-hint">Abra um resultado para consultar os detalhes e selecionar a mercadoria.</p><div class="result-list">' + items.map(item => '<button class="result-card ' + (selected?.codigo === item.codigo ? 'selected' : '') + '" data-detail="' + item.codigo + '" aria-label="Ver detalhes de ' + item.codigo + '"><div class="result-top"><span class="code">' + item.codigo + '</span><span class="tax-placeholder">' + (item.iva === null ? 'IVA indisponível' : 'IVA ' + item.iva + '%') + '</span></div><p>' + C.escape(item.descricao) + '</p><div class="result-bottom"><span>Cap. ' + item.codigo.slice(0, 2) + (chapterName(item.codigo) ? ' · ' + chapterName(item.codigo) : '') + '</span><strong>' + (selected?.codigo === item.codigo ? '✓ Selecionada' : 'Explorar detalhe ↗') + '</strong></div></button>').join('') + '</div>';
        if (paginated) {
            state.page = C.page(meta.current_page);
            $('pagination').innerHTML = '<button data-page="' + (state.page - 1) + '" ' + (state.page <= 1 ? 'disabled' : '') + '>← Anterior</button><span>Página ' + state.page + ' de ' + C.page(meta.last_page) + '</span><button data-page="' + (state.page + 1) + '" ' + (state.page >= meta.last_page ? 'disabled' : '') + '>Próxima →</button>';
        }
    }
    let lastResults = null;
    async function searchPauta(page = 1, restoring = '') {
        const query = $('searchInput').value.trim(), version = ++requestId;
        searchController?.abort(); searchController = new AbortController();
        hideSuggestions(); clearSelection(); state.q = query; state.page = C.page(page); state.searched = true; lastResults = null;
        renderChapters(); store();
        $('results').setAttribute('aria-busy', 'true');
        $('results').innerHTML = '<div class="empty-card" style="min-height:160px"><span class="pill">A consultar a pauta…</span><p style="margin-top:20px">A preparar os resultados da pesquisa.</p></div>';
        $('pagination').innerHTML = ''; $('results-count').textContent = '';
        try {
            const data = await json(C.searchURL(API, query, state.page), searchController.signal);
            if (version !== requestId) return;
            lastResults = data; displayResults(data); store();
            if (C.code(restoring)) await restoreSelection(restoring, version);
        } catch (error) {
            if (version !== requestId || error.name === 'AbortError') return;
            $('results-heading').textContent = 'Consulta indisponível';
            $('results-count').textContent = '';
            $('results').innerHTML = '<div class="error-empty"><h3>Não foi possível obter os resultados</h3><p>' + C.escape(error.message) + '</p><button class="secondary" data-retry>Tentar novamente</button></div>';
        } finally {
            if (version === requestId) $('results').setAttribute('aria-busy', 'false');
        }
    }
    async function getDetail(codigo, signal) {
        const data = await json(API.replace(/\/$/, '') + '/pauta/' + encodeURIComponent(codigo), signal);
        const value = C.item(data.data);
        if (!value || C.normalizedCode(value.codigo) !== C.normalizedCode(codigo)) throw new Error('Não foi possível confirmar esta mercadoria.');
        return value;
    }
    async function restoreSelection(codigo, version) {
        try {
            const value = await getDetail(codigo, searchController.signal);
            if (version !== requestId) return;
            selected = value; state.selected = value.codigo; renderGuide();
            if (lastResults) displayResults(lastResults);
            store();
        } catch (error) {
            if (version !== requestId || error.name === 'AbortError') return;
            $('guide').insertAdjacentHTML('beforeend', '<div class="guide-foot">Não foi possível recuperar a seleção anterior. Escolha novamente um resultado.</div>');
        }
    }
    function showModal() { $('detailModal').showModal(); }
    async function viewDetails(codigo, button) {
        if (!C.code(codigo)) return;
        hideSuggestions(); opener = button || document.activeElement; detail = null;
        const version = ++detailId; detailController?.abort(); detailController = new AbortController();
        $('modalTitle').textContent = 'Código: ' + codigo;
        $('modalContent').innerHTML = '<p class="muted">A carregar detalhes…</p>';
        showModal();
        try {
            const value = await getDetail(codigo, detailController.signal);
            if (version !== detailId) return;
            detail = value; $('modalTitle').textContent = value.codigo;
            const taxes = [['IVA', value.iva], ['IEQ', value.ieq]].map(([name, rate]) => '<div class="tax-box"><span>' + name + '</span><strong>' + (rate === null ? '—' : rate + '%') + '</strong>' + (rate === null ? '<p>Informação indisponível</p>' : '') + '</div>').join('');
            const optional = [['Unidade', value.unidade], ['Requisitos', value.requisitos], ['Observações', value.observacao]].filter(([, content]) => content).map(([label, content]) => '<div class="detail-field"><h3>' + label + '</h3><p class="pre-line">' + C.escape(content) + '</p></div>').join('');
            $('modalContent').innerHTML = '<p class="dialog-description">' + C.escape(value.descricao) + '</p><div class="dialog-tags"><span class="pill">Cap. ' + value.codigo.slice(0, 2) + '</span>' + (value.nivel === null ? '' : '<span class="pill">Nível ' + value.nivel + '</span>') + '</div><div class="tax-grid">' + taxes + '</div>' + optional + '<div class="note"><strong>Nota:</strong> Confirme sempre a classificação e o enquadramento com um despachante oficial.</div><div class="dialog-actions"><button class="primary" data-select>Usar esta mercadoria →</button><button class="secondary" data-close>Fechar</button></div>';
        } catch (error) {
            if (version !== detailId || error.name === 'AbortError') return;
            $('modalContent').innerHTML = '<div class="note">' + C.escape(error.message) + '</div><div class="dialog-actions"><button class="secondary" data-detail="' + codigo + '">Tentar novamente</button></div>';
        }
    }
    function select() {
        if (!detail) return;
        selected = detail; state.selected = detail.codigo; renderGuide();
        if (lastResults) displayResults(lastResults);
        $('detailModal').close(); store();
    }
    function clear() {
        requestId++; searchController?.abort(); hideSuggestions(); clearSelection();
        state = { q: '', page: 1, selected: '', searched: false };
        lastResults = null; $('searchInput').value = ''; renderChapters(); empty(); store(); $('searchInput').focus();
    }
    $('search-form').addEventListener('submit', event => { event.preventDefault(); searchPauta(); });
    $('clear-search').addEventListener('click', clear);
    $('searchInput').addEventListener('input', () => {
        hideSuggestions();
        const term = $('searchInput').value.trim(), version = suggestionId;
        if (term.length < 2 || term.length > 20) return;
        debounce = setTimeout(async () => {
            suggestionController = new AbortController();
            try {
                const params = new URLSearchParams({ termo: term });
                const data = await json(API + '/pauta/sugestoes?' + params, suggestionController.signal);
                if (version !== suggestionId) return;
                const items = C.collection(data);
                $('suggestions').innerHTML = items.map(item => '<button type="button" data-search="' + item.codigo + '"><b>' + item.codigo + '</b>' + C.escape(item.descricao) + '</button>').join('');
                $('suggestions').hidden = !items.length; $('searchInput').setAttribute('aria-expanded', String(!!items.length));
            } catch (error) { if (error.name !== 'AbortError' && version === suggestionId) hideSuggestions(); }
        }, 300);
    });
    $('searchInput').addEventListener('keydown', event => {
        if (event.key === 'Escape') hideSuggestions();
        if (event.key === 'ArrowDown' && !$('suggestions').hidden) { event.preventDefault(); $('suggestions').querySelector('button')?.focus(); }
    });
    document.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (button && !button.disabled) {
            const data = button.dataset;
            if (data.search !== undefined || data.capitulo !== undefined) { $('searchInput').value = data.search ?? data.capitulo; searchPauta(); }
            else if (data.page !== undefined) searchPauta(data.page);
            else if (data.detail) viewDetails(data.detail, button);
            else if (data.select !== undefined) select();
            else if (data.deselect !== undefined) { clearSelection(); if (lastResults) displayResults(lastResults); store(); $('searchInput').focus(); }
            else if (data.clear !== undefined) clear();
            else if (data.retry !== undefined) searchPauta(state.page);
            else if (data.close !== undefined) $('detailModal').close();
        }
        if (!event.target.closest('#search-form')) hideSuggestions();
    });
    $('detailModal').addEventListener('close', () => { detailId++; detailController?.abort(); if (opener?.isConnected) opener.focus(); });
    $('detailModal').addEventListener('click', event => {
        if (event.target === $('detailModal')) {
            const rect = $('detailModal').getBoundingClientRect();
            if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) $('detailModal').close();
        }
    });
    $('mobile-menu').addEventListener('click', () => {
        const opened = !$('nav').classList.contains('open');
        $('nav').classList.toggle('open', opened); $('mobile-menu').setAttribute('aria-expanded', String(opened));
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') { $('nav').classList.remove('open'); $('mobile-menu').setAttribute('aria-expanded', 'false'); }
    });
    async function loadStatistics() {
        try {
            const data = await json(API + '/pauta/estatisticas');
            const stats = data.data || {};
            $('statistics').textContent = [['Códigos', stats.total_codigos], ['Capítulos', stats.total_capitulos], ['Posições', stats.total_posicoes], ['Subposições', stats.total_subposicoes]].filter(([, value]) => Number.isInteger(value) && value >= 0).map(([label, value]) => value.toLocaleString('pt-AO') + ' ' + label.toLowerCase()).join(' · ') || 'Estatísticas indisponíveis.';
        } catch { $('statistics').textContent = 'Não foi possível carregar as estatísticas. A pesquisa continua disponível.'; }
    }
    if (!location.search) {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
            if (saved && typeof saved.q === 'string') state = { q: saved.q.slice(0, 100), page: C.page(saved.page), selected: C.code(saved.selected), searched: saved.searched === true };
        } catch {}
    }
    $('searchInput').value = state.q; renderChapters(); renderGuide(); loadStatistics();
    if (state.searched || state.selected) searchPauta(state.page, state.selected);
    else empty();
})();
