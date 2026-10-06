(function (root) {
    'use strict';
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[char]));
    const code = value => {
        const text = typeof value === 'string' ? value.trim() : '';
        return text.length >= 2 && text.length <= 20 && /^\d+(?:\.\d+)*$/.test(text) ? text : '';
    };
    const page = value => {
        const number = Number(value);
        return Number.isSafeInteger(number) && number > 0 ? number : 1;
    };
    const tax = value => {
        if (value === null || value === undefined || value === '') return null;
        const number = Number(value);
        return Number.isFinite(number) && number >= 0 ? number : null;
    };
    const text = value => typeof value === 'string' ? value : '';
    const normalizedCode = value => code(value).replace(/\./g, '');
    function item(value) {
        if (!value || !code(value.codigo) || typeof value.descricao !== 'string') return null;
        return {
            codigo: code(value.codigo), descricao: value.descricao,
            iva: tax(value.impostos?.iva ?? value.iva),
            ieq: tax(value.impostos?.ieq ?? value.ieq),
            unidade: text(value.unidade), requisitos: text(value.requisitos), observacao: text(value.observacao),
            nivel: Number.isInteger(value.nivel) ? value.nivel : null
        };
    }
    function collection(data) {
        const rows = Array.isArray(data?.data?.data) ? data.data.data : data?.data;
        return Array.isArray(rows) ? rows.map(item).filter(Boolean) : [];
    }
    function searchURL(base, query, currentPage) {
        const term = String(query || '').trim();
        if (term.length > 100) throw new Error('A pesquisa pode ter até 100 caracteres.');
        const params = new URLSearchParams();
        let endpoint = '/pauta';
        if (term && /^[\d.]+$/.test(term)) {
            if (!code(term)) throw new Error('Introduza um código pautal válido.');
            params.set('codigo', term);
            params.set('page', String(page(currentPage)));
        } else if (term) {
            if (term.length < 2) throw new Error('Introduza pelo menos dois caracteres.');
            endpoint += '/busca';
            params.set('q', term); params.set('tipo', 'descricao'); params.set('limit', '20');
        } else params.set('page', String(page(currentPage)));
        return base.replace(/\/$/, '') + endpoint + '?' + params.toString();
    }
    function readState(search) {
        const params = new URLSearchParams(search);
        return {
            q: (params.get('termo') || '').slice(0, 100),
            page: page(params.get('pagina')),
            selected: code(params.get('codigo_pautal')),
            searched: params.has('termo') || params.get('consulta') === '1'
        };
    }
    function consultationURL(base, state, selected = state.selected) {
        const url = new URL(base, 'https://logigate.invalid');
        ['termo', 'pagina', 'consulta', 'codigo_pautal'].forEach(key => url.searchParams.delete(key));
        if (state.searched) { url.searchParams.set('termo', state.q || ''); url.searchParams.set('consulta', '1'); }
        if (page(state.page) > 1) url.searchParams.set('pagina', String(page(state.page)));
        if (code(selected)) url.searchParams.set('codigo_pautal', code(selected));
        return url.pathname + url.search + url.hash;
    }
    function marketplaceURL(base, selected, state) {
        const url = new URL(base, 'https://logigate.invalid');
        const valid = item(selected);
        if (valid) {
            url.searchParams.set('codigo_pautal', valid.codigo);
            url.searchParams.set('mercadoria', valid.descricao.slice(0, 1000));
        }
        if (state?.searched) url.searchParams.set('pauta_termo', state.q || '');
        url.searchParams.set('pauta_pagina', String(page(state?.page)));
        return url.pathname + url.search;
    }
    function marketContext(search) {
        const params = new URLSearchParams(search);
        return {
            codigo: code(params.get('codigo_pautal')),
            q: (params.get('pauta_termo') || '').slice(0, 100),
            page: page(params.get('pauta_pagina')),
            searched: params.has('pauta_termo')
        };
    }
    const api = { escape, code, page, tax, item, collection, normalizedCode, searchURL, readState, consultationURL, marketplaceURL, marketContext };
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.LogiGatePauta = api;
})(typeof window !== 'undefined' ? window : globalThis);
