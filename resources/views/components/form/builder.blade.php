<!-- resources/views/components/form/builder.blade.php -->
@php
    use Illuminate\Support\Str;
    
    // Calcular classes de grid - versão inline para evitar erro de redeclaração
    $calculateGridClasses = function($size, $default = 6) {
        $size = $size ?? $default;
        
        // Se size for string com formato "col-span-X", usa como está
        if (str_starts_with($size, 'col-span-')) {
            return $size;
        }
        
        // Se size for um número (1-12), converte para classes Tailwind
        if (is_numeric($size)) {
            $size = intval($size);
            if ($size < 1) $size = 1;
            if ($size > 12) $size = 12;
            
            return "col-span-12 md:col-span-{$size}";
        }
        
        // Atalhos
        $shortcuts = [
            'full' => 'col-span-12',
            'half' => 'col-span-12 md:col-span-6',
            'third' => 'col-span-12 md:col-span-4',
            'quarter' => 'col-span-12 md:col-span-3',
            'two-thirds' => 'col-span-12 md:col-span-8',
            'three-quarters' => 'col-span-12 md:col-span-9',
            'auto' => 'col-span-12 md:col-span-6',
        ];
        
        return $shortcuts[$size] ?? 'col-span-12 md:col-span-6';
    };
    
    // Agrupar campos em linhas - versão inline
    $groupFieldsIntoRows = function($schema) {
        $rows = [];
        $currentRow = [];
        $currentRowSize = 0;
        $maxRowSize = 12; // 12 unidades por linha
        
        foreach ($schema as $name => $config) {
            $size = $config['size'] ?? $config['col'] ?? 6;
            
            // Converter string para número se necessário
            if (is_string($size) && is_numeric($size)) {
                $size = intval($size);
            } elseif (is_string($size)) {
                // Mapear atalhos para números
                $sizeMap = [
                    'full' => 12,
                    'half' => 6,
                    'third' => 4,
                    'quarter' => 3,
                    'two-thirds' => 8,
                    'three-quarters' => 9,
                    'auto' => 6,
                ];
                $size = $sizeMap[$size] ?? 6;
            }
            
            // Se o campo não couber na linha atual, começar nova linha
            if ($currentRowSize + $size > $maxRowSize && !empty($currentRow)) {
                $rows[] = $currentRow;
                $currentRow = [];
                $currentRowSize = 0;
            }
            
            $currentRow[$name] = $config;
            $currentRowSize += $size;
        }
        
        // Adicionar última linha se não estiver vazia
        if (!empty($currentRow)) {
            $rows[] = $currentRow;
        }
        
        return $rows;
    };
@endphp

@props([
    'schema' => [],
    'model' => null,
    'gridCols' => 12, // Sistema de 12 colunas
    'datalists' => [], // Novo prop para datalists
])

@php
    // Agrupar campos em linhas usando a closure
    $rows = $groupFieldsIntoRows($schema);

    // Extrair todos os datalists do schema
    $allDatalists = [];
    foreach ($schema as $config) {
        if (isset($config['datalist']) && isset($config['datalistOptions'])) {
            $allDatalists[$config['datalist']] = $config['datalistOptions'];
        }
    }
@endphp

<div class="space-y-6">
    {{-- Renderizar datalists primeiro --}}
    @foreach($allDatalists as $datalistId => $options)
        <datalist id="{{ $datalistId }}">
            @foreach($options as $option)
                <option value="{{ $option }}">
            @endforeach
        </datalist>
    @endforeach

    {{-- Renderizar campos em grid --}}
    @foreach($rows as $rowFields)
        <div class="grid grid-cols-12 gap-4">
            @foreach($rowFields as $name => $config)
                @php
                    $type        = $config['type'] ?? 'text';
                    $label       = $config['label'] ?? Str::of($name)->snake()->replace('_',' ')->title();
                    $placeholder = $config['placeholder'] ?? '';
                    $required    = $config['required'] ?? false;
                    $options     = $config['options'] ?? null;
                    $hint        = $config['hint'] ?? null;
                    $icon        = $config['icon'] ?? null;
                    $currency    = $config['currency'] ?? null;
                    $default     = $config['default'] ?? null;
                    $size        = $config['size'] ?? $config['col'] ?? 6;
                    
                    $gridClass = $calculateGridClasses($size);
                    
                    $value = old($name);
                    
                    if ($value === null && $model) {
                        $value = data_get($model, $name);
                    }
                    
                    if ($value === null && $default !== null) {
                        $value = $default;
                    }

                    // Verificar se tem datalistOptions no próprio campo
                    $datalistOptions = $config['datalistOptions'] ?? null;
                    if ($datalistOptions && isset($config['datalist'])) {
                        echo "<datalist id=\"{$config['datalist']}\">";
                        foreach ($datalistOptions as $option) {
                            echo "<option value=\"$option\">";
                        }
                        echo "</datalist>";
                    }
                @endphp

                <div class="{{ $gridClass }}">
                    @switch($type)
                        @case('section-heading')
                            <div class="mb-2 mt-6 first:mt-0">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $label }}</h3>
                                @if($hint)
                                    <p class="text-sm text-gray-500 mt-1">{{ $hint }}</p>
                                @endif
                            </div>
                            @break
                            
                        @case('divider')
                            <div class="my-4">
                                <hr class="border-gray-300">
                            </div>
                            @break
                            
                        @case('html')
                            {!! $config['html'] ?? '' !!}
                            @break
                            
                        @case('spacer')
                            {{-- Espaço vazio --}}
                            <div class="h-full"></div>
                            @break
                            
                        @default
                            <x-ui.input
                                :name="$name"
                                :type="$type"
                                :label="$label"
                                :placeholder="$placeholder"
                                :required="$required"
                                :hint="$hint"
                                :options="$options"
                                :currency="$currency"
                                :icon="$icon"
                                :value="$value"
                                
                                :model="$config['model'] ?? null"
                                :displayField="$config['displayField'] ?? null"
                                :extraField="$config['extraField'] ?? null"
                                :searchField="$config['searchField'] ?? null"
                                :where="$config['where'] ?? []"
                                :field="$config['field'] ?? $name"
                                
                                :rows="$config['rows'] ?? 3"
                                :maxlength="$config['maxlength'] ?? null"
                                :min="$config['min'] ?? null"
                                :max="$config['max'] ?? null"
                                :step="$config['step'] ?? null"
                                :disabled="$config['disabled'] ?? false"
                                :readonly="$config['readonly'] ?? false"
                                :prefix="$config['prefix'] ?? null"
                                :suffix="$config['suffix'] ?? null"
                            />
                    @endswitch
                </div>
            @endforeach
        </div>
    @endforeach
</div>