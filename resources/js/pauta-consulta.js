const root = document.querySelector('.pauta-page');
if (root && !root.dataset.pautaInitialized) {
    root.dataset.pautaInitialized = 'true';
    const bootstrap = JSON.parse(document.querySelector('#pauta-bootstrap').textContent);
    const form = document.querySelector('#pauta-search-form');
    const input = form.elements.q;
    const type = form.elements.tipo;
    const size = form.elements.per_page;
    const results = document.querySelector('#results');
    const pagination = document.querySelector('#pagination');
    const status = document.querySelector('#pauta-status');
    const loading = document.querySelector('#loading');
    const suggestions = document.querySelector('#suggestions');
    const modal = document.querySelector('#detailModal');
    const title = document.querySelector('#modalTitle');
    const content = document.querySelector('#modalContent');
    let searchSequence = 0, detailSequence = 0, suggestionSequence = 0;
    let searchAbort, detailAbort, suggestionAbort, timer, active = -1, choices = [], trigger;

    function element(tag, text, className) {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    }
    function hideSuggestions() {
        suggestionSequence++; suggestionAbort?.abort(); clearTimeout(timer);
        suggestions.hidden = true; suggestions.replaceChildren(); choices = []; active = -1;
        input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); input.removeAttribute('aria-busy');
    }
    function queryParams(page = 1) {
        const params = new URLSearchParams({tipo: type.value, per_page: size.value, page: String(page)});
        const q = input.value.trim(); if (q) params.set('q', q);
        return params;
    }
    function pageURL(params) {
        const url = new URL(form.action);
        const context = new URLSearchParams(location.search);
        ['months', 'location', 'recurrence'].forEach(key => { if (context.has(key)) url.searchParams.set(key, context.get(key)); });
        params.forEach((value, key) => url.searchParams.set(key, value));
        return url;
    }
    function selectionURL(item) {
        const url = new URL(location.href);
        url.searchParams.set('pauta_id', item.id); url.searchParams.set('codigo', item.codigo);
        return url;
    }
    function rate(value) { return value === null || value === undefined || value === '' ? 'Não indicado na fonte' : typeof value === 'number' ? `${value}%` : String(value); }
    function renderListing(data) {
        results.replaceChildren(); pagination.replaceChildren();
        const items = data.data;
        if (!items.length) {
            const empty = data.meta.catalogue_empty ? 'O catálogo pautal ainda não tem dados disponíveis.' : data.meta.total > 0 ? 'Esta página já não tem resultados.' : 'Não encontrámos mercadorias para esta pesquisa.';
            results.append(element('h2', empty), element('p', 'Ajuste a descrição ou o código, ou limpe os filtros.'));
            status.textContent = empty;
        } else {
            results.append(element('p', `${items.length} mercadorias nesta página · ${data.meta.total} resultados no total.`, 'pauta-count'));
            const list = element('div', undefined, 'pauta-result-list');
            items.forEach(item => {
                const card = element('article', undefined, 'result-card');
                const heading = element('h3');
                const code = element('a', String(item.codigo));
                const link = element('a', `Consultar detalhes de ${item.codigo} →`, 'pauta-detail-link');
                [code, link].forEach(anchor => { anchor.href = selectionURL(item); anchor.dataset.pautaSelect = ''; anchor.dataset.id = item.id; anchor.dataset.code = item.codigo; });
                heading.append(code);
                card.append(heading, element('p', item.descricao), element('p', `IVA: ${rate(item.impostos?.iva)}`, 'pauta-result-rate'), link);
                list.append(card);
            });
            results.append(list);
            status.textContent = `${data.meta.total} resultados. Página ${data.meta.current_page} de ${data.meta.last_page}.`;
        }
        function pageLink(page, label) {
            const anchor = element('a', label); anchor.dataset.pautaPage = page; anchor.href = pageURL(queryParams(page)); pagination.append(anchor);
        }
        if (!items.length && data.meta.total > 0) pageLink(1, 'Primeira página');
        if (data.meta.current_page > 1) pageLink(data.meta.current_page - 1, 'Anterior');
        if (data.meta.total > 0) pagination.append(element('span', `Página ${data.meta.current_page} de ${data.meta.last_page}`));
        if (data.meta.current_page < data.meta.last_page) pageLink(data.meta.current_page + 1, 'Seguinte');
    }
    function closeDetails(updateURL = true) {
        detailSequence++; detailAbort?.abort();
        document.dispatchEvent(new CustomEvent('pauta:changing'));
        modal.classList.remove('active');
        if (updateURL) {
            const url = new URL(location.href);
            ['pauta_id', 'codigo', 'mercadoria_descricao'].forEach(key => url.searchParams.delete(key));
            history.replaceState(null, '', url);
        }
        if (trigger?.isConnected) trigger.focus();
    }
    async function search(page = 1, updateURL = true) {
        if (input.value.trim().length === 1) { status.textContent = 'Escreva pelo menos dois caracteres para pesquisar.'; input.focus(); return; }
        hideSuggestions(); closeDetails(false);
        const current = ++searchSequence; searchAbort?.abort(); searchAbort = new AbortController();
        results.replaceChildren(); pagination.replaceChildren(); results.setAttribute('aria-busy', 'true');
        loading.hidden = false; status.textContent = 'A pesquisar mercadorias…';
        const params = queryParams(page);
        if (updateURL) history.pushState(null, '', pageURL(params));
        try {
            const response = await fetch(`/api/v1/pauta?${params}`, {signal: searchAbort.signal, cache:'no-store', headers:{Accept:'application/json'}});
            const data = await response.json(); if (current !== searchSequence) return;
            if (!response.ok || data.success !== true) {
                const message = response.status === 422 ? Object.values(data.errors || {}).flat().join(' ') : '';
                results.append(element('h2', 'Não foi possível carregar os resultados.'), element('p', message || 'Tente novamente. A indisponibilidade não significa ausência de requisitos ou taxas.'));
                status.textContent = 'Pesquisa indisponível.'; return;
            }
            renderListing(data);
        } catch (error) {
            if (current !== searchSequence || error.name === 'AbortError') return;
            results.append(element('h2', 'Não foi possível carregar os resultados.'), element('p', 'Verifique a ligação e tente novamente.'));
            status.textContent = 'Erro de ligação.';
        } finally {
            if (current === searchSequence) { loading.hidden = true; results.removeAttribute('aria-busy'); }
        }
    }
    function renderDetail(item) {
        title.textContent = `Código: ${item.codigo}`; content.replaceChildren();
        const body = element('div', undefined, 'pauta-detail-body');
        function section(label, text, className) { const section = element('section', undefined, className); section.append(element('h3', label), element('p', text)); body.append(section); return section; }
        section('Descrição', item.descricao, 'pauta-description');
        const rates = element('dl', undefined, 'pauta-rates');
        [['Regime geral', item.regime_geral], ['SADC', item.sadc], ['UA', item.ua], ['IVA', item.impostos?.iva], ['IEQ', item.impostos?.ieq]].forEach(([label, value]) => {
            const row = element('div'); row.append(element('dt', label), element('dd', rate(value))); rates.append(row);
        });
        body.append(rates);
        [['Unidade',item.unidade],['Requisitos registados',item.requisitos],['Observações',item.observacao]].forEach(([label,value])=>{if(value !== null && value !== undefined && value !== '') section(label,String(value));});
        const source = section('Sobre esta informação', item.fonte.designacao, 'pauta-source');
        source.append(element('p', item.fonte.limitacao));
        if (item.fonte.atualizacao_registo) source.append(element('p', `Actualização técnica do registo: ${item.fonte.atualizacao_registo}. Não é uma data de vigência.`));
        source.append(element('p', 'A pesquisa não valida oficialmente a classificação da mercadoria. Confirme o enquadramento aplicável à sua operação.'));
        content.append(body);
    }
    async function viewDetails(code, id = null, updateURL = true) {
        hideSuggestions(); trigger = document.activeElement;
        const current = ++detailSequence; detailAbort?.abort(); detailAbort = new AbortController();
        document.dispatchEvent(new CustomEvent('pauta:changing'));
        modal.classList.add('active'); title.textContent = 'A carregar mercadoria…'; content.replaceChildren(element('p', 'A consultar os dados da fonte…')); title.focus();
        const url = id ? `${modal.dataset.detailsEndpoint}/${encodeURIComponent(id)}?codigo=${encodeURIComponent(code)}` : `/api/v1/pauta/${encodeURIComponent(code)}`;
        try {
            const response = await fetch(url, {signal:detailAbort.signal, cache:'no-store', headers:{Accept:'application/json'}});
            const data = await response.json(); if (current !== detailSequence) return;
            if (!response.ok || !data.success) throw new Error('selection');
            if (id && String(data.data.id) !== String(id)) throw new Error('identity');
            renderDetail(data.data);
            if (updateURL) history.replaceState(null, '', selectionURL(data.data));
            document.dispatchEvent(new CustomEvent('pauta:selected', {detail:{codigo:String(data.data.codigo), pauta_id:data.data.id, descricao:data.data.descricao}}));
        } catch (error) {
            if (current !== detailSequence || error.name === 'AbortError') return;
            title.textContent = 'Não foi possível consultar esta selecção';
            content.replaceChildren(element('p', 'Tente seleccionar novamente um resultado da pauta. Não foi apresentada experiência para esta selecção.'));
        }
    }
    function activate(index) {
        active = index;
        [...suggestions.children].forEach((node, position) => node.setAttribute('aria-selected', String(position === active)));
        if (active >= 0) { input.setAttribute('aria-activedescendant', `pauta-option-${active}`); suggestions.children[active]?.scrollIntoView({block:'nearest'}); }
        else input.removeAttribute('aria-activedescendant');
    }
    async function selectSuggestion(item) {
        // Commit the typed query and its results before opening the explicitly chosen identity.
        await search(1);
        await viewDetails(item.codigo,item.id);
    }
    input.addEventListener('input', () => {
        hideSuggestions(); const q = input.value.trim(); if (q.length < 2) return;
        timer = setTimeout(async () => {
            const current = ++suggestionSequence; suggestionAbort = new AbortController(); input.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(`/api/v1/pauta/sugestoes?${new URLSearchParams({termo:q,tipo:type.value})}`, {signal:suggestionAbort.signal,headers:{Accept:'application/json'}});
                const data = await response.json(); if (current !== suggestionSequence) return;
                if (!response.ok || !data.success) return;
                choices = data.data; suggestions.replaceChildren();
                choices.forEach((item,index) => {
                    const option = element('div', `${item.codigo} · ${item.descricao}`);
                    option.id = `pauta-option-${index}`; option.setAttribute('role','option'); option.setAttribute('aria-selected','false');
                    option.addEventListener('mousedown',event=>event.preventDefault()); option.addEventListener('click',()=>selectSuggestion(item)); suggestions.append(option);
                });
                suggestions.hidden = choices.length === 0; input.setAttribute('aria-expanded',String(choices.length > 0));
            } catch {} finally { if(current === suggestionSequence) input.removeAttribute('aria-busy'); }
        },300);
    });
    input.addEventListener('keydown',event=>{
        if (event.key === 'Escape') { hideSuggestions(); return; }
        if (suggestions.hidden) return;
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); activate((active + (event.key === 'ArrowDown' ? 1 : -1) + choices.length) % choices.length); }
        if (event.key === 'Enter' && active >= 0) { event.preventDefault(); const item = choices[active]; selectSuggestion(item); }
    });
    input.addEventListener('blur',()=>setTimeout(hideSuggestions,150));
    form.addEventListener('submit',event=>{event.preventDefault(); search(1);});
    [type,size].forEach(control=>control.addEventListener('change',()=>search(1)));
    document.querySelector('#pauta-clear').addEventListener('click',event=>{
        event.preventDefault(); form.reset(); input.value=''; type.value='auto'; size.value='20'; history.replaceState(null,'',form.action); search(1,false); input.focus();
    });
    results.addEventListener('click',event=>{const link=event.target.closest('[data-pauta-select]');if(link){event.preventDefault();viewDetails(link.dataset.code,link.dataset.id);}});
    pagination.addEventListener('click',event=>{const link=event.target.closest('[data-pauta-page]');if(link){event.preventDefault();search(Number(link.dataset.pautaPage));}});
    document.querySelector('[data-pauta-close]').addEventListener('click',event=>{event.preventDefault();closeDetails();});
    window.addEventListener('popstate',()=>{
        const params=new URLSearchParams(location.search); input.value=params.get('q')||'';type.value=params.get('tipo')||'auto';size.value=params.get('per_page')||'20';
        search(Number(params.get('page')||1),false).then(()=>{if(params.get('codigo'))viewDetails(params.get('codigo'),params.get('pauta_id'),false);});
    });
    // Compatibility for the existing guide/modal keyboard controller and local QA.
    window.viewDetails=viewDetails; window.closeModal=closeDetails; window.searchPauta=search;
    queueMicrotask(()=>{
        if(bootstrap.selection){title.focus();document.dispatchEvent(new CustomEvent('pauta:selected',{detail:{codigo:bootstrap.selection.codigo,pauta_id:bootstrap.selection.id,descricao:bootstrap.selection.descricao}}));}
    });
}
