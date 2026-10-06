// Search is server-rendered; menu and newsletter retain their existing behavior.
const root = document.querySelector('.marketplace-page');
if (root && !root.dataset.mpInitialized) {
    root.dataset.mpInitialized = 'true';
    const codeFilter = root.querySelector('.mp-filters input[name="codigo"]');
    codeFilter?.addEventListener('input', () => { const identity = codeFilter.form.querySelector('[name="pauta_id"]'); if (identity) identity.disabled = true; });
    root.classList.add('mp-js');
    const toggle = root.querySelector('.mp-menu-toggle');
    const menu = root.querySelector('.mp-mobile-nav');
    function closeMenu(focus = false) {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menu');
        if (focus) toggle.focus();
    }
    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        if (open) menu.querySelector('a').focus();
    });
    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeMenu()));
    root.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu.classList.contains('is-open')) closeMenu(true);
    });
    root.addEventListener('click', event => { if (!event.target.closest('.mp-header')) closeMenu(); });
    root.querySelector('.mp-header').addEventListener('focusout', event => {
        if (event.relatedTarget && !event.currentTarget.contains(event.relatedTarget)) closeMenu();
    });
    matchMedia('(min-width:1200px)').addEventListener('change', event => { if (event.matches) closeMenu(); });

    const form = root.querySelector('.mp-newsletter');
    const feedback = root.querySelector('#newsletter-feedback');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.getAttribute('aria-busy') === 'true') return;
        const button = form.querySelector('button');
        const email = form.elements.namedItem('email');
        form.setAttribute('aria-busy', 'true');
        button.disabled = true;
        email.removeAttribute('aria-invalid');
        feedback.dataset.state = 'loading';
        feedback.textContent = 'A enviar o pedido de subscrição…';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok || data.success !== true) {
                feedback.dataset.state = 'error';
                feedback.textContent = Object.values(data.errors || {}).flat().join(' ') || data.message || 'Não foi possível enviar. Tente novamente.';
                if (data.errors?.email) { email.setAttribute('aria-invalid', 'true'); email.focus(); }
                return;
            }
            feedback.dataset.state = 'success';
            feedback.textContent = 'Pedido de subscrição recebido. Consulte o seu email para confirmar; se não receber a mensagem, contacte o apoio.';
            form.reset();
        } catch {
            feedback.dataset.state = 'error';
            feedback.textContent = 'Não foi possível confirmar o envio. Verifique a ligação e tente novamente.';
        } finally {
            form.removeAttribute('aria-busy');
            button.disabled = false;
        }
    });
}
