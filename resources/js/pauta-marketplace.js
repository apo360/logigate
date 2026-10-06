import '../css/pauta-marketplace.css';
import './pauta-consulta.js';
const guide = document.querySelector('#pauta-marketplace-guide');
let sequence = 0;
let controller;
function reset() { sequence++; controller?.abort(); if (guide) { guide.hidden = true; guide.replaceChildren(); } }
document.addEventListener('pauta:changing', reset);
document.addEventListener('keydown', event => {
    const modal = document.querySelector('#detailModal.active');
    if (!modal) return;
    if (event.key === 'Escape') { window.closeModal?.(); return; }
    if (event.key !== 'Tab') return;
    const links = [...modal.querySelectorAll('button,a[href],input,[tabindex="0"]')].filter(el => el.getClientRects().length);
    const first = links[0], last = links.at(-1);
    if (!first) return;
    if (event.shiftKey && (document.activeElement === first || document.activeElement.id === 'modalTitle')) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
});
document.addEventListener('pauta:selected', async event => {
    if (!guide) return;
    reset();
    const current = sequence;
    controller = new AbortController();
    guide.hidden = false;
    const heading = document.createElement('h3'); heading.textContent = 'Despachantes com experiência nesta mercadoria';
    const status = document.createElement('p'); status.setAttribute('role', 'status'); status.textContent = 'A consultar histórico público…';
    guide.append(heading, status);
    const params = new URLSearchParams({ codigo: event.detail.codigo });
    const context = new URLSearchParams(location.search);
    ['months', 'location', 'recurrence'].forEach(key => { if (context.has(key)) params.set(key, context.get(key)); });
    if (event.detail.pauta_id) params.set('pauta_id', event.detail.pauta_id);
    if (event.detail.descricao) params.set('mercadoria_descricao', event.detail.descricao);
    ['q','tipo','page','per_page'].forEach(key => { if (context.has(key)) params.set(`pauta_${key}`, context.get(key)); });
    try {
        const response = await fetch(`${guide.dataset.endpoint}?${params}`, { signal: controller.signal, headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (current !== sequence) return;
        if (!response.ok) throw new Error('selection');
        status.textContent = data.profiles.length ? 'Correspondência com a mercadoria seleccionada.' : 'Sem histórico público autorizado disponível. Isso não significa ausência de experiência profissional.';
        data.profiles.slice(0, 3).forEach(profile => {
            const card = document.createElement('article');
            const name = document.createElement('h4'); name.textContent = profile.public_name;
            const place = document.createElement('p'); place.textContent = profile.public_location || 'Localização pública não indicada';
            const reason = document.createElement('p'); reason.textContent = profile.operations !== null ? `Código exacto · actividade em ${profile.active_months} meses · ${profile.operations} processos concluídos.` : 'Especialidade declarada. Sem histórico público disponível neste período.';
            card.append(name, place, reason); guide.append(card);
        });
        const link = document.createElement('a'); link.href = data.marketplace_url; link.textContent = 'Ver todos no marketplace →'; guide.append(link);
        const start = new Date(`${data.period.start}T12:00:00Z`);
        const end = new Date(`${data.period.end_exclusive}T12:00:00Z`); end.setUTCDate(end.getUTCDate() - 1);
        const format = date => date.toLocaleDateString('pt-AO', {timeZone:'UTC'});
        const note = document.createElement('p'); note.textContent = `Histórico registado no LogiGate: ${format(start)} a ${format(end)}. O guia não valida oficialmente a classificação pautal nem garante qualidade ou prazo.`; guide.append(note);
        const url = new URL(location.href); url.searchParams.set('codigo', data.selection.codigo); url.searchParams.set('pauta_id', data.selection.id); history.replaceState(null, '', url);
    } catch (error) {
        if (current !== sequence || error.name === 'AbortError') return;
        status.textContent = 'Não foi possível consultar o histórico desta selecção. Tente seleccionar a mercadoria novamente.';
        const link = document.createElement('a'); link.href = guide.dataset.marketplace; link.textContent = 'Explorar o marketplace →'; guide.append(link);
    }
});
