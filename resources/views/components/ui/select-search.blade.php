@props(['name','label'=>null,'options'=>[]])

<div>
    @if($label)<label class="block text-xs font-semibold mb-1">{{ $label }}</label>@endif
    <div class="relative">
        <input type="text" placeholder="Pesquisar..." x-data x-on:input.debounce.400="$dispatch('input', $event.target.value)" wire:ignore
            x-on:input="$dispatch('input', $event.target.value)"
            class="w-full px-3 py-2 border rounded" />
        <select id="{{ $name }}" name="{{ $name }}" wire:model.defer="{{ 'form.'.$name }}" class="w-full mt-2 p-2 hidden">
            @foreach($options as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('selectSearch', () => ({
            init() {
                const select = document.getElementById('{{ $name }}');
                const input = this.$el.querySelector('input');

                input.addEventListener('input', () => {
                    const filter = input.value.toLowerCase();
                    Array.from(select.options).forEach(option => {
                        option.style.display = option.text.toLowerCase().includes(filter) ? '' : 'none';
                    });
                });
            }
        }));
    });
</script>