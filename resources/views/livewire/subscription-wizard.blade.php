<div>
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Progress Steps Melhorado -->
    <div class="mb-12">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
            @foreach(['Escolher Plano', 'Personalizar', 'Finalizar'] as $index => $stepName)
                @php 
                    $stepNumber = $index + 1;
                    $isActive = $step === $stepNumber;
                    $isCompleted = $step > $stepNumber;
                @endphp
                <div class="flex items-center w-full md:w-auto">
                    <div class="relative">
                        <!-- Círculo do passo -->
                        <div class="{{ $isActive ? 'bg-logigate-primary border-logigate-primary' : ($isCompleted ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300') }} 
                                    h-12 w-12 rounded-full border-2 flex items-center justify-center transition-all duration-300">
                            
                            @if($isCompleted)
                                <i class="fas fa-check text-white text-lg"></i>
                            @else
                                <span class="{{ $isActive ? 'text-white font-bold' : 'text-gray-500' }} text-lg">
                                    {{ $stepNumber }}
                                </span>
                            @endif
                        </div>
                        
                        <!-- Linha conectora (exceto último) -->
                        @if($stepNumber < 3)
                            <div class="hidden md:block absolute top-6 left-12 w-32 {{ $isCompleted ? 'bg-green-500' : 'bg-gray-200' }} h-0.5"></div>
                        @endif
                    </div>
                    
                    <!-- Nome do passo -->
                    <div class="ml-4">
                        <div class="text-xs uppercase tracking-wider text-gray-500">Passo {{ $stepNumber }}</div>
                        <div class="{{ $isActive ? 'text-logigate-primary font-bold' : ($isCompleted ? 'text-green-600 font-medium' : 'text-gray-600') }} 
                                    text-sm md:text-base transition-colors">
                            {{ $stepName }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Progress bar mobile -->
        <div class="mt-6 md:hidden">
            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-logigate-primary transition-all duration-500" 
                     style="width: {{ (($step - 1) / 2) * 100 }}%"></div>
            </div>
            <div class="text-xs text-gray-500 text-center mt-2">
                {{ round((($step - 1) / 2) * 100) }}% completo
            </div>
        </div>
    </div>

    <!-- Step 1: Escolher Plano - Design Card Premium -->
    @if($step === 1)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Selecione o seu plano</h2>
            <p class="text-gray-600">Escolha o plano ideal para o crescimento do seu negócio</p>
        </div>
        
        <!-- Toggle Anual/Mensal (antes dos cards) -->
        <div class="flex justify-center mb-10">
            <div class="inline-flex items-center bg-white rounded-xl shadow-sm p-1 border border-gray-200">
                <button type="button"
                        wire:click="$set('modalidade', 'mensal')"
                        class="px-6 py-3 text-sm font-medium rounded-lg transition-all duration-300 
                               {{ $modalidade === 'mensal' 
                                  ? 'bg-logigate-primary text-white shadow-md' 
                                  : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Pagamento Mensal
                </button>
                <button type="button"
                        wire:click="$set('modalidade', 'anual')"
                        class="px-6 py-3 text-sm font-medium rounded-lg transition-all duration-300 
                               {{ $modalidade === 'anual' 
                                  ? 'bg-logigate-primary text-white shadow-md' 
                                  : 'text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas fa-calendar-star mr-2"></i>
                    Pagamento Anual
                    <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                        -10%
                    </span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            @foreach($planos as $plano)
                @php
                    $isSelected = $planoSelecionado == $plano->id;
                    $isPopular = $plano->nome === 'Profissional';
                    $price = $modalidade === 'anual' ? $plano->preco_anual : $plano->preco_mensal;
                    $monthlyEquivalent = $modalidade === 'anual' ? $price / 12 : $price;
                    $annualSavings = $modalidade === 'anual' ? ($plano->preco_mensal * 12) - $plano->preco_anual : 0;
                @endphp
                
                <div class="relative">
                    <!-- Badge Popular -->
                    @if($isPopular)
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                            <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg">
                                <i class="fas fa-crown mr-1"></i> MAIS POPULAR
                            </span>
                        </div>
                    @endif
                    
                    <div class="h-full border-2 {{ $isSelected ? 'border-logigate-primary' : 'border-gray-200' }} 
                                {{ $isPopular ? 'ring-2 ring-yellow-200' : '' }}
                                rounded-2xl overflow-hidden bg-white hover:shadow-xl transition-all duration-300
                                transform hover:-translate-y-1 cursor-pointer"
                         wire:click="$set('planoSelecionado', {{ $plano->id }})">
                        
                        <!-- Header com gradiente -->
                        <div class="bg-gradient-to-r {{ $isSelected 
                            ? 'from-logigate-primary to-logigate-secondary' 
                            : ($isPopular ? 'from-gray-800 to-gray-900' : 'from-gray-100 to-gray-200') }} 
                            p-6 text-center">
                            <h3 class="text-xl font-bold {{ $isSelected || $isPopular ? 'text-white' : 'text-gray-900' }}">
                                {{ $plano->nome }}
                            </h3>
                            @if($plano->descricao)
                                <p class="text-sm {{ $isSelected || $isPopular ? 'text-white/90' : 'text-gray-600' }} mt-1">
                                    {{ $plano->descricao }}
                                </p>
                            @endif
                        </div>
                        
                        <!-- Preço -->
                        <div class="p-6 text-center border-b">
                            <div class="mb-2">
                                <span class="text-4xl font-bold text-gray-900">
                                    {{ number_format($monthlyEquivalent, 0) }}
                                </span>
                                <span class="text-gray-500 ml-1">Kz</span>
                            </div>
                            <div class="text-sm text-gray-500">
                                por mês{{ $modalidade === 'anual' ? ' (anual)' : '' }}
                            </div>
                            
                            @if($modalidade === 'anual' && $annualSavings > 0)
                                <div class="mt-3 inline-flex items-center bg-green-50 text-green-700 px-3 py-1 rounded-full text-sm">
                                    <i class="fas fa-piggy-bank mr-2"></i>
                                    Poupa {{ number_format($annualSavings, 0) }} Kz/ano
                                </div>
                            @endif
                        </div>
                        
                        <!-- Recursos -->
                        <div class="p-6">
                            <ul class="space-y-4">
                                <li class="flex items-center">
                                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-green-600 text-xs"></i>
                                    </div>
                                    <span class="text-gray-700">{{ $plano->limite_utilizadores }} utilizadores</span>
                                </li>
                                
                                <li class="flex items-center">
                                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-database text-blue-600 text-xs"></i>
                                    </div>
                                    <span class="text-gray-700">{{ $plano->limite_armazenamento_gb }} GB armazenamento</span>
                                </li>
                                
                                @foreach($plano->modulos->take(3) as $modulo)
                                    <li class="flex items-center">
                                        <div class="flex-shrink-0 h-6 w-6 rounded-full bg-logigate-primary/10 flex items-center justify-center mr-3">
                                            <i class="fas fa-check text-logigate-primary text-xs"></i>
                                        </div>
                                        <span class="text-gray-700">{{ $modulo->nome }}</span>
                                    </li>
                                @endforeach
                                
                                @if($plano->modulos->count() > 3)
                                    <li class="text-center">
                                        <span class="text-sm text-logigate-primary cursor-pointer hover:underline">
                                            +{{ $plano->modulos->count() - 3 }} mais módulos
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        
                        <!-- Botão de seleção -->
                        <div class="p-6 pt-0">
                            <button class="w-full py-3 px-4 rounded-lg font-medium transition-all duration-300
                                          {{ $isSelected 
                                             ? 'bg-logigate-primary text-white hover:bg-logigate-dark shadow-md' 
                                             : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                @if($isSelected)
                                    <i class="fas fa-check-circle mr-2"></i> Selecionado
                                @else
                                    Selecionar Plano
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Ação -->
        <div class="mt-12 pt-8 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    @if($planoSelecionado)
                        @php
                            $selectedPlano = $planos->firstWhere('id', $planoSelecionado);
                        @endphp
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 inline-block">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-blue-500 text-xl mr-3"></i>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $selectedPlano->nome }} selecionado</div>
                                    <div class="text-sm text-gray-600">
                                        {{ number_format($modalidade === 'anual' ? $selectedPlano->preco_anual/12 : $selectedPlano->preco_mensal, 0) }} Kz/mês
                                        @if($modalidade === 'anual')
                                            <span class="text-green-600 ml-2">
                                                ({{ number_format($selectedPlano->preco_anual, 0) }} Kz/ano)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <button wire:click="proximoPasso"
                        wire:loading.attr="disabled"
                        class="bg-logigate-primary from-logigate-primary to-logigate-secondary 
                               text-white px-8 py-4 rounded-xl font-semibold text-lg
                               hover:shadow-lg hover:scale-[1.02] transition-all duration-300
                               disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        @if(!$planoSelecionado) disabled @endif>
                    
                    <span wire:loading.remove wire:target="proximoPasso">
                        Continuar para Personalização
                        <i class="fas fa-arrow-right ml-2"></i>
                    </span>
                    
                    <span wire:loading wire:target="proximoPasso">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Processando...
                    </span>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 2: Personalização - Design Moderno -->
    @if($step === 2)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Conteúdo Principal -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Header -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Personalize a sua subscrição</h2>
                    <p class="text-gray-600">Adicione módulos e recursos extras conforme as suas necessidades</p>
                </div>

                <!-- Módulos Adicionais -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Módulos Adicionais</h3>
                                <p class="text-sm text-gray-600 mt-1">Expanda as funcionalidades do sistema</p>
                            </div>
                            <button wire:click="toggleAllModulos"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 
                                           rounded-lg text-sm font-medium transition-colors">
                                @if(count($modulosSelecionados) === count($modulosDisponiveis))
                                    <i class="fas fa-times mr-2"></i> Desmarcar Todos
                                @else
                                    <i class="fas fa-check-square mr-2"></i> Selecionar Todos
                                @endif
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        @if(!empty($modulosDisponiveis))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($modulosDisponiveis as $modulo)
                                    @php
                                        $isSelected = in_array($modulo['id'], $modulosSelecionados);
                                    @endphp
                                    <div class="border rounded-xl p-4 hover:border-logigate-primary transition-all duration-300
                                                {{ $isSelected ? 'border-logigate-primary bg-blue-50' : 'border-gray-200' }}">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex items-start space-x-3">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-lg 
                                                            {{ $isSelected ? 'bg-logigate-primary' : 'bg-gray-100' }} 
                                                            flex items-center justify-center">
                                                    <i class="{{ $modulo['icone'] ?? 'fas fa-cube' }} 
                                                              {{ $isSelected ? 'text-white' : 'text-gray-600' }}"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-medium text-gray-900">{{ $modulo['module_name'] }}</h4>
                                                    @if($modulo['description'])
                                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                                            {{ $modulo['description'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-gray-900">
                                                    {{ number_format($modulo['price'] ?? 0, 2) }} Kz
                                                </div>
                                                <div class="text-xs text-gray-500">/mês</div>
                                            </div>
                                        </div>
                                        
                                        <button wire:click="toggleModulo({{ $modulo['id'] }})"
                                                class="w-full py-2 px-4 rounded-lg text-sm font-medium transition-all duration-300
                                                       {{ $isSelected 
                                                          ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' 
                                                          : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200' }}">
                                            @if($isSelected)
                                                <i class="fas fa-minus-circle mr-2"></i> Remover
                                            @else
                                                <i class="fas fa-plus-circle mr-2"></i> Adicionar
                                            @endif
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-box-open text-gray-400 text-2xl"></i>
                                </div>
                                <h4 class="text-gray-700 font-medium">Nenhum módulo disponível</h4>
                                <p class="text-gray-500 text-sm mt-1">Todos os módulos estão incluídos no seu plano</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recursos Adicionais -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Recursos Adicionais</h3>
                        <p class="text-sm text-gray-600 mt-1">Ajuste a capacidade conforme necessário</p>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Usuários Adicionais -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-logigate-primary transition-colors">
                            <div class="mb-4 md:mb-0">
                                <div class="flex items-center mb-2">
                                    <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-users text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Utilizadores Adicionais</h4>
                                        <p class="text-sm text-gray-600">Adicione mais utilizadores à sua conta</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center bg-gray-50 rounded-lg p-1">
                                    <button wire:click="decrementarUsuarios"
                                            class="h-10 w-10 rounded-lg flex items-center justify-center 
                                                   text-gray-600 hover:bg-white hover:shadow disabled:opacity-50"
                                            @if($usuariosExtras <= 0) disabled @endif>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <div class="w-16 text-center">
                                        <div class="text-2xl font-bold text-gray-900">{{ $usuariosExtras }}</div>
                                        <div class="text-xs text-gray-500">utilizadores</div>
                                    </div>
                                    <button wire:click="incrementarUsuarios"
                                            class="h-10 w-10 rounded-lg flex items-center justify-center 
                                                   text-gray-600 hover:bg-white hover:shadow">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="text-right">
                                    <div class="text-xl font-bold text-gray-900">
                                        {{ number_format($usuariosExtras * 500, 0) }} Kz
                                    </div>
                                    <div class="text-xs text-gray-500">500 Kz/usuário/mês</div>
                                </div>
                            </div>
                        </div>

                        <!-- Armazenamento Adicional -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 border border-gray-200 rounded-xl hover:border-logigate-primary transition-colors">
                            <div class="mb-4 md:mb-0">
                                <div class="flex items-center mb-2">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-database text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Armazenamento Adicional</h4>
                                        <p class="text-sm text-gray-600">Adicione mais espaço de armazenamento</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center bg-gray-50 rounded-lg p-1">
                                    <button wire:click="decrementarArmazenamento"
                                            class="h-10 w-10 rounded-lg flex items-center justify-center 
                                                   text-gray-600 hover:bg-white hover:shadow disabled:opacity-50"
                                            @if($armazenamentoExtra <= 0) disabled @endif>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <div class="w-16 text-center">
                                        <div class="text-2xl font-bold text-gray-900">{{ $armazenamentoExtra }}</div>
                                        <div class="text-xs text-gray-500">GB</div>
                                    </div>
                                    <button wire:click="incrementarArmazenamento"
                                            class="h-10 w-10 rounded-lg flex items-center justify-center 
                                                   text-gray-600 hover:bg-white hover:shadow">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="text-right">
                                    <div class="text-xl font-bold text-gray-900">
                                        {{ number_format($armazenamentoExtra * 100, 0) }} Kz
                                    </div>
                                    <div class="text-xs text-gray-500">100 Kz/GB/mês</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Resumo -->
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <!-- Resumo do Pedido -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">
                        <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-6">
                            <h3 class="text-lg font-bold text-white">Resumo do Pedido</h3>
                            <p class="text-gray-300 text-sm mt-1">Confira os detalhes da sua subscrição</p>
                        </div>
                        
                        <div class="p-6">
                            @if($resumo && isset($resumo['plano']))
                                <!-- Plano Selecionado -->
                                <div class="mb-6 pb-6 border-b border-gray-200">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $resumo['plano']['nome'] }}</h4>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                       bg-logigate-primary/10 text-logigate-primary mt-1">
                                                {{ ucfirst($modalidade) }}
                                            </span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-gray-900">
                                                {{ number_format($resumo['plano']['preco'], 0) }} Kz
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $modalidade === 'anual' ? '/ano' : '/mês' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Itens Adicionais -->
                                <div class="space-y-4">
                                    @if(isset($resumo['modulos']) && !empty($resumo['modulos']))
                                        <div>
                                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                                <span>Módulos Adicionais</span>
                                                <span>{{ count($resumo['modulos']) }} itens</span>
                                            </div>
                                            @foreach($resumo['modulos'] as $modulo)
                                                <div class="flex justify-between text-sm mb-1">
                                                    <span class="text-gray-700 truncate">{{ $modulo['nome'] }}</span>
                                                    <span class="font-medium">{{ number_format($modulo['preco'], 0) }} Kz</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(isset($resumo['usuarios']))
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="text-sm text-gray-700">Utilizadores Extra</div>
                                                <div class="text-xs text-gray-500">{{ $resumo['usuarios']['quantidade'] }} unidades</div>
                                            </div>
                                            <div class="font-medium">{{ number_format($resumo['usuarios']['preco'], 0) }} Kz</div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($resumo['armazenamento']))
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <div class="text-sm text-gray-700">Armazenamento Extra</div>
                                                <div class="text-xs text-gray-500">{{ $resumo['armazenamento']['gb'] }} GB</div>
                                            </div>
                                            <div class="font-medium">{{ number_format($resumo['armazenamento']['preco'], 0) }} Kz</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Total -->
                                <div class="mt-8 pt-6 border-t border-gray-200">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700">Subtotal</span>
                                        <span class="font-medium">{{ number_format($total, 0) }} Kz</span>
                                    </div>
                                    
                                    @if($modalidade === 'anual')
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-green-600">Desconto Anual (10%)</span>
                                            <span class="text-green-600 font-medium">
                                                -{{ number_format($total * 0.1, 0) }} Kz
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-gray-900">Total Final</span>
                                            <span class="text-2xl font-bold text-logigate-primary">
                                                {{ number_format($total * ($modalidade === 'anual' ? 0.9 : 1), 0) }} Kz
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            {{ $modalidade === 'anual' ? 'Cobrança anual' : 'Cobrança mensal' }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-3"></i>
                                    <p class="text-gray-500">Selecione um plano para ver o resumo</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Navegação -->
                    <div class="space-y-3">
                        <button wire:click="passoAnterior"
                                class="w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-xl 
                                       font-medium hover:bg-gray-50 transition-colors flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar
                        </button>
                        
                        <button wire:click="proximoPasso"
                                class="w-full bg-logigate-primary from-logigate-primary to-logigate-secondary 
                                       text-white px-4 py-3 rounded-xl font-semibold hover:shadow-lg 
                                       transition-all duration-300 flex items-center justify-center">
                            Continuar para Pagamento
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Step 3: Pagamento - Design Premium -->
    @if($step === 3)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Finalizar Subscrição</h2>
            <p class="text-gray-600">Complete os dados para ativar os seus serviços</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Métodos de Pagamento -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Métodos -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Método de Pagamento</h3>
                        <p class="text-sm text-gray-600 mt-1">Escolha como deseja realizar o pagamento</p>
                    </div>
                    
                    <div class="p-6">
                        @if(!empty($metodosPagamento))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($metodosPagamento as $metodo)
                                    @php
                                        $isSelected = $metodoPagamento == $metodo->id;
                                    @endphp
                                    <div class="border-2 {{ $isSelected ? 'border-logigate-primary ring-2 ring-logigate-primary/20' : 'border-gray-200' }} 
                                                rounded-xl p-5 cursor-pointer hover:border-logigate-primary/50 
                                                transition-all duration-300"
                                         wire:click="$set('metodoPagamento', {{ $metodo->id }})">
                                        
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-12 rounded-lg 
                                                        {{ $isSelected ? 'bg-logigate-primary' : 'bg-gray-100' }} 
                                                        flex items-center justify-center mr-4">
                                                @if($metodo->icone)
                                                    <img src="{{ asset($metodo->icone) }}" class="h-6 w-6">
                                                @else
                                                    <i class="fas fa-credit-card {{ $isSelected ? 'text-white' : 'text-gray-600' }}"></i>
                                                @endif
                                            </div>
                                            
                                            <div class="flex-1">
                                                <h4 class="font-bold text-gray-900">{{ $metodo->metodo }}</h4>
                                                @if($metodo->descricao)
                                                    <p class="text-sm text-gray-600 mt-1">{{ $metodo->descricao }}</p>
                                                @endif
                                            </div>
                                            
                                            @if($isSelected)
                                                <div class="ml-4">
                                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($metodo->instrucoes && $isSelected)
                                            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                                                <p class="text-sm text-blue-800">{{ $metodo->instrucoes }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-credit-card text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-500">Nenhum método de pagamento disponível no momento</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Termos e Condições -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Termos e Condições</h3>
                        <p class="text-sm text-gray-600 mt-1">Leia atentamente antes de prosseguir</p>
                    </div>
                    
                    <div class="p-6">
                        <div class="prose max-w-none text-gray-600 mb-6">
                            <p class="font-medium mb-3">Ao subscrever o serviço Logigate, concorda com:</p>
                            <ul class="space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span>A subscrição renova automaticamente no final de cada período</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span>Pode cancelar a qualquer momento, sem penalizações</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span>O cancelamento será efetivo no final do período atual</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink-0"></i>
                                    <span>Reembolsos apenas para pagamentos anuais, nos primeiros 14 dias</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                            <input type="checkbox" 
                                   id="aceitaTermos"
                                   wire:model="aceitaTermos"
                                   class="mt-1 mr-3 h-5 w-5 text-logigate-primary rounded 
                                          focus:ring-logigate-primary focus:ring-2">
                            <label for="aceitaTermos" class="text-sm text-gray-700">
                                Eu li e concordo com os 
                                <a href="#" class="text-logigate-primary hover:underline font-medium">Termos de Serviço</a> 
                                e 
                                <a href="#" class="text-logigate-primary hover:underline font-medium">Política de Privacidade</a>
                                do Logigate
                            </label>
                        </div>
                        
                        @error('aceitaTermos')
                            <div class="mt-3 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Resumo Final -->
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <!-- Resumo -->
                    <div class="bg-gradient-to-br from-green-400 to-blue-400 rounded-2xl shadow-xl overflow-hidden mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-white mb-6">Resumo Final</h3>
                            
                            @if($resumo && isset($resumo['plano']))
                                <!-- Plano -->
                                <div class="mb-6 pb-6 border-b border-white/20">
                                    <div class="flex justify-between items-center mb-3">
                                        <div>
                                            <div class="text-white font-semibold">{{ $resumo['plano']['nome'] }}</div>
                                            <div class="text-gray-300 text-sm">{{ ucfirst($modalidade) }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-white">
                                                {{ number_format($resumo['plano']['preco'], 0) }} Kz
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Extras -->
                                <div class="space-y-4 mb-6">
                                    @if(isset($resumo['modulos']) && !empty($resumo['modulos']))
                                        <div>
                                            <div class="text-sm text-gray-300 mb-2">Módulos Adicionais</div>
                                            @foreach($resumo['modulos'] as $modulo)
                                                <div class="flex justify-between text-sm text-gray-400 mb-1">
                                                    <span class="truncate">{{ $modulo['nome'] }}</span>
                                                    <span>{{ number_format($modulo['preco'], 0) }} Kz</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    @if(isset($resumo['usuarios']))
                                        <div class="flex justify-between items-center">
                                            <div class="text-sm text-gray-300">{{ $resumo['usuarios']['quantidade'] }} utilizadores extra</div>
                                            <div class="text-white font-medium">{{ number_format($resumo['usuarios']['preco'], 0) }} Kz</div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($resumo['armazenamento']))
                                        <div class="flex justify-between items-center">
                                            <div class="text-sm text-gray-300">{{ $resumo['armazenamento']['gb'] }} GB extra</div>
                                            <div class="text-white font-medium">{{ number_format($resumo['armazenamento']['preco'], 0) }} Kz</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Total -->
                                <div class="pt-6 border-t border-white/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-300">Subtotal</span>
                                        <span class="text-white font-bold">{{ number_format($total, 0) }} Kz</span>
                                    </div>
                                    
                                    @if($modalidade === 'anual')
                                        <div class="flex justify-between items-center text-sm mb-3">
                                            <span class="text-green-300">Desconto Anual</span>
                                            <span class="text-green-300 font-medium">
                                                -{{ number_format($total * 0.1, 0) }} Kz
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <div class="mt-4 pt-4 border-t border-white/20">
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-white">Total a Pagar</span>
                                            <span class="text-3xl font-bold text-white">
                                                {{ number_format($total * ($modalidade === 'anual' ? 0.9 : 1), 0) }} Kz
                                            </span>
                                        </div>
                                        <div class="text-gray-300 text-sm mt-2">
                                            {{ $modalidade === 'anual' ? 'Pagamento anual' : 'Pagamento mensal' }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Ações -->
                    <div class="space-y-4">
                        <button wire:click="passoAnterior"
                                class="w-full border border-gray-300 text-gray-700 px-4 py-3 rounded-xl 
                                       font-medium hover:bg-gray-50 transition-colors flex items-center justify-center">
                            <i class="fas fa-arrow-left mr-2"></i> Voltar para Personalização
                        </button>
                        
                        <button wire:click="finalizarSubscricao"
                                wire:target="finalizarSubscricao"
                                class="w-full bg-gradient-to-r from-green-500 to-green-600 
                                       text-white px-4 py-4 rounded-xl font-bold text-lg
                                       hover:shadow-xl hover:scale-[1.02] transition-all duration-300
                                       flex items-center justify-center">
                            
                            <span wire:loading.remove wire:target="finalizarSubscricao">
                                <i class="fas fa-lock mr-3"></i>
                                Finalizar Subscrição
                            </span>
                            
                            <span wire:loading wire:target="finalizarSubscricao">
                                <i class="fas fa-spinner fa-spin mr-3"></i>
                                Processando...
                            </span>
                        </button>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center text-sm text-gray-500">
                                <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                                Pagamento 100% seguro
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações de Segurança -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3 flex-shrink-0"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">O que acontece depois?</p>
                                <p class="text-xs">Após confirmar, será gerada uma referência de pagamento. Os serviços serão ativados automaticamente após confirmação do pagamento.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>



<!-- CSS Adicional -->
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Animações */
@keyframes pulse-glow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.animate-pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}

/* Scroll personalizado */
.prose::-webkit-scrollbar {
    width: 6px;
}

.prose::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.prose::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.prose::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>
</div>