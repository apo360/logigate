<div class="p-6">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-logigate-dark">Menu Builder</h2>

        <div class="flex items-center gap-3">
            <button wire:click="create" class="px-3 py-2 rounded bg-logigate-primary text-white shadow">
                + Novo Menu
            </button>
            <button id="save-order-btn" class="px-3 py-2 rounded border border-logigate-primary text-logigate-primary">
                Guardar Ordem
            </button>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <!-- Árvore / Canvas -->
        <div class="col-span-2">
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500 mb-3">Arraste os menus para reordenar ou arraste um item para dentro de outro para criar submenus.</p>

                <div id="menu-canvas">
                    @if(empty($menusTree))
                        <div class="py-6 text-center text-gray-500">Sem menus.</div>
                    @else
                        <ul id="root-list" class="space-y-2">
                            @foreach($menusTree as $node)
                                @include('livewire.partials.menu-node', ['node' => $node])
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Painel lateral: lista e edição rápida -->
        <div>
            <div class="bg-white rounded shadow p-4 mb-4">
                <h3 class="font-semibold mb-2">Menus Rápidos</h3>
                <div class="space-y-2">
                    @foreach($flatMenus as $m)
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div class="text-sm">{{ $m['menu_name'] }}</div>
                            <div class="flex gap-2">
                                <button wire:click="edit({{ $m['id'] }})" class="text-logigate-primary text-sm">Editar</button>
                                <button wire:click="delete({{ $m['id'] }})" class="text-red-600 text-sm">Eliminar</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded shadow p-4">
                <h3 class="font-semibold mb-2">Ajuda</h3>
                <p class="text-xs text-gray-500">Arraste e solte. Clique em "Salvar Ordem" quando terminar.</p>
            </div>
        </div>
    </div>

    <!-- Modal: Create / Edit -->
    <div x-data="{ open: @entangle('showModal') }" x-cloak>
        <div x-show="open" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg w-3/4 max-w-2xl p-6 shadow">
                <h3 class="text-lg font-semibold mb-4">{{ $menuId ? 'Editar menu' : 'Novo menu' }}</h3>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm">Nome</label>
                        <input type="text" wire:model.defer="menu_name" class="w-full px-3 py-2 border rounded mt-1" />
                        @error('menu_name') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-sm">Rota</label>
                        <input type="text" wire:model.defer="route" class="w-full px-3 py-2 border rounded mt-1" />
                        @error('route') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Ícone (fa classes)</label>
                        <input type="text" wire:model.defer="icon" class="w-full px-3 py-2 border rounded mt-1" />
                    </div>

                    <div>
                        <label class="text-sm">Permissão</label>
                        <input type="text" wire:model.defer="permission" class="w-full px-3 py-2 border rounded mt-1" />
                    </div>

                    <div>
                        <label class="text-sm">Módulo</label>
                        <select wire:model.defer="module_id" class="w-full px-3 py-2 border rounded mt-1">
                            <option value="">-- Selecionar --</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod['id'] }}">{{ $mod['module_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm">Pai</label>
                        <select wire:model.defer="parent_id" class="w-full px-3 py-2 border rounded mt-1">
                            <option value="">-- Nenhum --</option>
                            @foreach($flatMenus as $m)
                                <option value="{{ $m['id'] }}">{{ $m['menu_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="text-sm">Descrição</label>
                        <textarea wire:model.defer="description" class="w-full px-3 py-2 border rounded mt-1"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button @click="open = false" class="px-3 py-2 rounded border">Cancelar</button>
                    <button wire:click="save" class="px-3 py-2 rounded bg-logigate-primary text-white">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates and JS -->
    @push('styles')
    <style>
        /* small adjustments for nested lists */
        ul.root, ul.node-children { list-style: none; padding-left: 0; }
    </style>
    @endpush

    @push('scripts')
    <!-- SortableJS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
    document.addEventListener('livewire:load', function () {
        // recursive initialization helper
        function initSortable(container) {
            return new Sortable(container, {
                group: 'menus',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                onEnd: function (evt) {
                    // no-op here (we will use Save Order button to collect)
                }
            });
        }

        // initialize Sortable on every list container (root and children)
        function attachAll() {
            document.querySelectorAll('.sortable-list').forEach(function (el) {
                if (!el.dataset.sortable) {
                    initSortable(el);
                    el.dataset.sortable = "1";
                }
            });
        }

        attachAll();

        // Re-attach after Livewire updates
        Livewire.hook('message.processed', (message, component) => {
            attachAll();
        });

        // Save order when clicking button: we will walk DOM to gather node tree
        const saveBtn = document.getElementById('save-order-btn');
        saveBtn.addEventListener('click', function () {
            const root = document.getElementById('root-list');
            const nodes = [];

            function walkList(ul, parentId = null) {
                Array.from(ul.children).forEach((li, index) => {
                    const id = li.dataset.id ? parseInt(li.dataset.id) : null;
                    if (!id) return;
                    nodes.push({
                        id: id,
                        parent_id: parentId,
                        order: index
                    });

                    // find child UL
                    const childUl = li.querySelector(':scope > ul.node-children');
                    if (childUl) {
                        walkList(childUl, id);
                    }
                });
            }

            walkList(root, null);

            // call Livewire method
            Livewire.emit('saveOrder', nodes);
        });

        // Listen to notify events and show toast
        window.addEventListener('notify', e => {
            // simple toast (better to use toastr or sweetalert)
            alert(e.detail.message);
        });
    });
    </script>
    @endpush

</div>

