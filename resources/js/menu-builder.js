const builders = new Map();

window.initMenuBuilder = wire => {
    const root = wire.$el;
    const id = root.getAttribute('wire:id');
    builders.get(id)?.destroy();
    const instances = new Map();
    function attach() {
        for (const [element, sortable] of instances) {
            if (!root.contains(element)) { sortable.destroy(); instances.delete(element); }
        }
        if (!window.Sortable) return;
        root.querySelectorAll('.sortable-list').forEach(element => {
            if (instances.has(element)) return;
            instances.set(element, new window.Sortable(element, {
                group: 'menus', animation: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 150,
                draggable: '>li', fallbackOnBody: true, swapThreshold: 0.65,
            }));
        });
    }
    async function save(event) {
        const button = event.target.closest('[data-save-menu-order]');
        if (!button || button.disabled) return;
        const list = root.querySelector('#root-list');
        if (!list) return;
        const nodes = [];
        function walk(ul, parent = null) {
            for (const li of ul.children) {
                const id = Number(li.dataset.id);
                if (!Number.isInteger(id) || id < 1) continue;
                nodes.push({ id, parent_id: parent, order: nodes.filter(node => node.parent_id === parent).length });
                const child = li.querySelector(':scope > ul.node-children');
                if (child) walk(child, id);
            }
        }
        walk(list);
        if (!nodes.length) return;
        button.disabled = true;
        try { await wire.$call('saveOrder', nodes); }
        catch { window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Não foi possível guardar a ordem. Verifique os dados e tente novamente.' } })); }
        finally { button.disabled = false; }
    }
    root.addEventListener('click', save);
    const destroy = () => {
        root.removeEventListener('click', save);
        for (const instance of instances.values()) instance.destroy();
        instances.clear(); builders.delete(id);
    };
    builders.set(id, { attach, destroy });
    attach();
};

function registerHooks() {
    Livewire.hook('component.init', ({ component, cleanup }) => {
        cleanup(() => builders.get(component.id)?.destroy());
    });
    Livewire.hook('morphed', ({ component }) => builders.get(component.id)?.attach());
}
if (window.Livewire) registerHooks();
else document.addEventListener('livewire:init', registerHooks, { once: true });
