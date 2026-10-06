(() => {
    const header = document.querySelector('[data-website-header]');
    if (!header || header.dataset.enhanced) return;
    header.dataset.enhanced = 'true';
    const toggle = header.querySelector('.website-menu-toggle');
    const menu = header.querySelector('.website-navigation');
    toggle.hidden = false;
    const close = (focus = false) => {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menu');
        if (focus) toggle.focus();
    };
    toggle.addEventListener('click', () => {
        const open = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        if (open) menu.querySelector('a')?.focus();
    });
    menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => close()));
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && menu.classList.contains('is-open')) close(true); });
    document.addEventListener('click', event => { if (!header.contains(event.target)) close(); });
    header.addEventListener('focusout', event => { if (event.relatedTarget && !header.contains(event.relatedTarget)) close(); });
    matchMedia('(min-width:1280px)').addEventListener('change', () => close());
    // The landing already enhances all data-json-form forms, including this one.
    const form = document.querySelector('[data-website-newsletter]');
    if (!form || document.body.classList.contains('lg-landing')) return;
    const feedback = document.getElementById('newsletter-feedback');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.getAttribute('aria-busy') === 'true') return;
        const button = form.querySelector('button');
        const email = form.elements.namedItem('email');
        form.setAttribute('aria-busy', 'true'); button.disabled = true;
        email.removeAttribute('aria-invalid'); feedback.textContent = 'A enviar o pedido de subscrição…';
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok || data.success !== true) {
                feedback.textContent = Object.values(data.errors || {}).flat().join(' ') || 'Não foi possível enviar. Tente novamente.';
                if (data.errors?.email) { email.setAttribute('aria-invalid', 'true'); email.focus(); }
            } else { feedback.textContent = 'Pedido de subscrição recebido. Consulte o seu email para confirmar.'; form.reset(); }
        } catch { feedback.textContent = 'Não foi possível confirmar o envio. Verifique a ligação e tente novamente.'; }
        finally { form.removeAttribute('aria-busy'); button.disabled = false; }
    });
})();
