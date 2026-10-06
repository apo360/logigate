(() => {
    const form = document.querySelector('[data-portal-login]');
    if (!form || form.dataset.enhanced) return;
    form.dataset.enhanced = 'true';
    const input = form.querySelector('#password');
    const toggle = form.querySelector('[data-password-toggle]');
    toggle.hidden = false;
    toggle.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        toggle.textContent = visible ? 'Ocultar' : 'Mostrar';
        toggle.setAttribute('aria-pressed', String(visible));
    });
})();
