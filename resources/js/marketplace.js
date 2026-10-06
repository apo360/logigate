// Search is server-rendered; shared website chrome owns menu and newsletter.
const root = document.querySelector('.marketplace-page');
if (root && !root.dataset.mpInitialized) {
    root.dataset.mpInitialized = 'true';
    const codeFilter = root.querySelector('.mp-filters input[name="codigo"]');
    codeFilter?.addEventListener('input', () => { const identity = codeFilter.form.querySelector('[name="pauta_id"]'); if (identity) identity.disabled = true; });
}
