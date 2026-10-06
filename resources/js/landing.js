import { initLandingMotion } from './landing-motion';

document.documentElement.classList.add('js');

const toggle = document.querySelector('.menu-toggle');
const menu = document.getElementById('mobile-menu');
function closeMenu(returnFocus = false) {
    menu?.classList.remove('is-open');
    toggle?.setAttribute('aria-expanded', 'false');
    toggle?.setAttribute('aria-label', 'Abrir menu');
    if (returnFocus) toggle?.focus();
}
toggle?.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    if (open) menu.querySelector('a')?.focus();
});
menu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeMenu()));
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && menu?.classList.contains('is-open')) closeMenu(true);
});
document.addEventListener('click', event => {
    if (!event.target.closest('.site-header')) closeMenu();
});
document.querySelector('.site-header')?.addEventListener('focusout', event => {
    if (event.relatedTarget && !event.currentTarget.contains(event.relatedTarget)) closeMenu();
});
window.matchMedia('(min-width:1200px)').addEventListener('change', event => {
    if (event.matches) closeMenu();
});

const tabs = [...document.querySelectorAll('.demo-tabs a')];
const tablist = document.querySelector('.demo-tabs');
tablist?.setAttribute('role', 'tablist');
function selectTab(tab, focus = false) {
    tabs.forEach(item => {
        const active = item === tab;
        item.setAttribute('aria-selected', String(active));
        item.tabIndex = active ? 0 : -1;
        document.getElementById(item.getAttribute('aria-controls')).hidden = !active;
    });
    document.querySelectorAll('[data-demo-nav]').forEach(item => {
        item.classList.toggle('active', item.dataset.demoNav === tab.dataset.demo);
    });
    if (focus) tab.focus();
}
tabs.forEach(tab => {
    tab.setAttribute('role', 'tab');
    const panel = document.getElementById(tab.getAttribute('aria-controls'));
    panel.setAttribute('role', 'tabpanel');
    panel.setAttribute('aria-labelledby', tab.id);
    panel.tabIndex = 0;
    tab.addEventListener('click', event => { event.preventDefault(); selectTab(tab); });
    tab.addEventListener('keydown', event => {
        const index = tabs.indexOf(tab);
        const targets = { ArrowRight: (index + 1) % tabs.length, ArrowLeft: (index + tabs.length - 1) % tabs.length, Home: 0, End: tabs.length - 1 };
        if (event.key in targets) { event.preventDefault(); selectTab(tabs[targets[event.key]], true); }
    });
});
if (tabs.length) selectTab(tabs[0]);

const labels = { monthly: 'por mês', semestral: 'por semestre', annual: 'por ano' };
const formatter = new Intl.NumberFormat('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
function updateBilling(cycle) {
    document.querySelectorAll('.plan').forEach(plan => {
        const price = plan.querySelector('.plan-price');
        const value = price.dataset[cycle];
        price.textContent = value === '' ? 'Preço não disponível' : `${formatter.format(Number(value))} AOA`;
        plan.querySelector('.cycle-label').textContent = labels[cycle];
        plan.querySelector('.billing-cycle').value = cycle;
        plan.querySelector('button[type=submit]').disabled = value === '';
    });
}
document.querySelectorAll('[name=billing]').forEach(input => input.addEventListener('change', () => updateBilling(input.value)));
const selectedBilling = document.querySelector('[name=billing]:checked');
if (selectedBilling) updateBilling(selectedBilling.value);
document.querySelectorAll('.plan form').forEach(form => form.addEventListener('submit', () => {
    form.querySelector('.billing-cycle').value = document.querySelector('[name=billing]:checked').value;
}));

document.querySelectorAll('[data-json-form]').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type=submit]');
        const feedback = document.getElementById(form.dataset.feedback);
        const original = button.textContent;
        form.querySelectorAll('[aria-invalid]').forEach(field => { field.removeAttribute('aria-invalid'); field.removeAttribute('aria-describedby'); });
        button.disabled = true;
        button.textContent = 'A enviar…';
        form.setAttribute('aria-busy', 'true');
        feedback.dataset.state = 'loading';
        feedback.textContent = 'A enviar o seu pedido…';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok || data.success !== true) {
                feedback.dataset.state = 'error';
                feedback.textContent = Object.values(data.errors || {}).flat().join(' ') || data.message || 'Não foi possível enviar. Verifique os dados e tente novamente.';
                let first;
                Object.keys(data.errors || {}).forEach(name => {
                    const field = form.elements.namedItem(name);
                    if (field) { field.setAttribute('aria-invalid', 'true'); field.setAttribute('aria-describedby', feedback.id); first ||= field; }
                });
                first?.focus();
                return;
            }
            feedback.dataset.state = 'success';
            // The endpoints confirm persistence; email delivery is handled separately.
            feedback.textContent = form.id === 'contact-form' ? 'Pedido recebido. A equipa entrará em contacto consigo.' : 'Pedido de subscrição recebido. Consulte o seu email para confirmar; se não receber a mensagem, contacte o apoio.';
            form.reset();
        } catch {
            feedback.dataset.state = 'error';
            feedback.textContent = 'Não foi possível confirmar o envio. Verifique a ligação e tente novamente.';
        } finally {
            form.removeAttribute('aria-busy');
            button.disabled = false;
            button.textContent = original;
        }
    });
});

initLandingMotion();
