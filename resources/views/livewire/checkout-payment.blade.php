{{-- resources/views/livewire/checkout-payment.blade.php --}}
<div>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-gray-100 py-8 px-4">
        <div class="max-w-6xl mx-auto">
            {{-- Cabeçalho --}}
            <div class="text-center mb-8">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-400 rounded-xl flex items-center justify-center">
                        <img 
                            src="{{ asset('dist/img/LOGIGATE.png') }}" 
                            alt="LogiGate" 
                            class="hidden md:block max-w-[70px] opacity-80 transition-all duration-300 hover:animate-spin"
                        >
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Logi<span class="text-blue-600">Gate</span></h1>
                        <p class="text-sm text-gray-500">Finalizar Pagamento</p>
                    </div>
                </div>
                
                {{-- Progresso --}}
                <div class="max-w-md mx-auto mb-6">
                    <div class="flex items-center justify-between mb-2">
                        {{-- Passo 1 --}}
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm">
                                <i class="fas fa-check"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700">Cadastro</span>
                        </div>
                        <div class="flex-1 h-1 bg-green-500 mx-2"></div>
                        
                        {{-- Passo 2 --}}
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $paymentStatus === 'paid' ? 'bg-green-500' : 'bg-blue-600' }} text-white flex items-center justify-center text-sm">
                                @if($paymentStatus === 'paid')
                                    <i class="fas fa-check"></i>
                                @else
                                    2
                                @endif
                            </div>
                            <span class="ml-2 text-sm font-medium {{ $paymentStatus === 'paid' ? 'text-green-600' : 'text-blue-600' }}">
                                Pagamento
                            </span>
                        </div>
                        <div class="flex-1 h-1 {{ $paymentStatus === 'paid' ? 'bg-green-500' : 'bg-gray-300' }} mx-2"></div>
                        
                        {{-- Passo 3 --}}
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full {{ $paymentStatus === 'paid' ? 'bg-blue-600' : 'bg-gray-300' }} text-white flex items-center justify-center text-sm">
                                3
                            </div>
                            <span class="ml-2 text-sm font-medium {{ $paymentStatus === 'paid' ? 'text-blue-600' : 'text-gray-500' }}">
                                Activação
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Coluna Esquerda - Formulário --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                        @if($error)
                            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                                    <span class="text-red-800">{{ $error }}</span>
                                </div>
                            </div>
                        @endif
                        
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Escolha o Método de Pagamento</h2>
                        
                        {{-- Resumo do Plano --}}
                        <div class="bg-gray-50 rounded-xl p-4 mb-8">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ $plano->nome }}</h3>
                                    <p class="text-sm text-gray-600">{{ ucfirst($cycle) }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ number_format($amount, 0, ',', '.') }} AOA
                                    </div>
                                    <p class="text-sm text-gray-500">Inclui IVA 14%</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Métodos de Pagamento --}}
                        @if(!$response)
                        <div class="mb-8">
                            <div class="grid grid-cols-3 gap-4">
                                {{-- GPO --}}
                                <button type="button"
                                    class="payment-method p-4 rounded-xl border-2 transition-all {{ $method === 'GPO' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}"
                                    wire:click="$set('method', 'GPO')"
                                    wire:loading.attr="disabled">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                                            <i class="fas fa-mobile-alt text-green-600 text-xl"></i>
                                        </div>
                                        <h4 class="font-medium text-gray-900">Multicaixa GPO</h4>
                                        <p class="text-sm text-gray-600">Pagamento com telefone</p>
                                        @if($method === 'GPO')
                                            <div class="mt-2">
                                                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full"></span>
                                            </div>
                                        @endif
                                    </div>
                                </button>
                                
                                {{-- REF --}}
                                <button type="button"
                                    class="payment-method p-4 rounded-xl border-2 transition-all {{ $method === 'REF' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}"
                                    wire:click="$set('method', 'REF')"
                                    wire:loading.attr="disabled">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                                            <i class="fas fa-qrcode text-purple-600 text-xl"></i>
                                        </div>
                                        <h4 class="font-medium text-gray-900">Multicaixa REF</h4>
                                        <p class="text-sm text-gray-600">Pagamento por referência</p>
                                        @if($method === 'REF')
                                            <div class="mt-2">
                                                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full"></span>
                                            </div>
                                        @endif
                                    </div>
                                </button>
                                
                                {{-- Transferência --}}
                                <button type="button"
                                    class="payment-method p-4 rounded-xl border-2 transition-all {{ $method === 'TRANSFER' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}"
                                    wire:click="$set('method', 'TRANSFER')"
                                    wire:loading.attr="disabled">
                                    <div class="flex flex-col items-center text-center">
                                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mb-3">
                                            <i class="fas fa-university text-yellow-600 text-xl"></i>
                                        </div>
                                        <h4 class="font-medium text-gray-900">Transferência</h4>
                                        <p class="text-sm text-gray-600">Bancária</p>
                                        @if($method === 'TRANSFER')
                                            <div class="mt-2">
                                                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full"></span>
                                            </div>
                                        @endif
                                    </div>
                                </button>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Campos do Formulário --}}
                        @if(!$response)
                        <div class="space-y-6">
                            @if($showPhoneField)
                                <div class="fade-in">
                                    <label class="block text-gray-700 mb-2 font-medium">Telefone (GPO)</label>
                                    <div class="flex">
                                        <div class="w-20 bg-gray-100 rounded-l-xl border border-gray-300 border-r-0 flex items-center justify-center">
                                            <span class="text-gray-700">+244</span>
                                        </div>
                                        <input type="tel" 
                                            wire:model="phone"
                                            class="flex-1 px-4 py-3 border border-gray-300 rounded-r-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                            placeholder="9XX XXX XXX"
                                            maxlength="9"
                                            {{ $method !== 'GPO' ? 'disabled' : '' }}>
                                    </div>
                                    @error('phone')
                                        <span class="text-red-600 text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                    <p class="text-gray-500 text-sm mt-2">
                                        Receberá um SMS para confirmar o pagamento.
                                    </p>
                                </div>
                            @endif
                            
                            @if($showRefInfo || $showTransferInfo)
                                <div class="fade-in">
                                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-info-circle text-green-600 mr-3 mt-1"></i>
                                            <div>
                                                <p class="text-green-800 text-sm">
                                                    @if($showRefInfo)
                                                        Será gerada uma referência Multicaixa para pagamento.
                                                    @elseif($showTransferInfo)
                                                        Serão fornecidos dados bancários para transferência.
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if ($errors->any())
                                <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4">
                                    <ul class="text-sm list-disc pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Botão de Confirmação --}}
                            <div class="pt-6 border-t">
                                <button 
                                    wire:click="submit" 
                                    wire:loading.attr="disabled" 
                                    type="button"
                                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-4 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                                    <span wire:loading.remove wire:target="submit">
                                        @if($method === 'GPO')
                                            <i class="fas fa-paper-plane mr-2"></i>Enviar Pedido GPO
                                        @elseif($method === 'REF')
                                            <i class="fas fa-qrcode mr-2"></i>Gerar Referência
                                        @elseif($method === 'TRANSFER')
                                            <i class="fas fa-university mr-2"></i>Obter Dados Bancários
                                        @else
                                            Selecionar Método
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Processando...
                                    </span>
                                </button>
                                
                                <p class="text-center text-gray-500 text-sm mt-4">
                                    <i class="fas fa-lock mr-1"></i> Pagamento 100% seguro • 
                                    <i class="fas fa-shield-alt mr-1 ml-3"></i> Dados criptografados
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    {{-- Informações de Suporte --}}
                    <div class="bg-white rounded-2xl shadow-xl p-6">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-headset text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-2">Precisa de ajuda?</h3>
                                <p class="text-gray-600 text-sm mb-3">
                                    Nossa equipe está disponível para ajudá-lo com qualquer dúvida sobre o pagamento.
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <a href="tel:+244948242262" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-phone mr-1"></i> +244 948 242 262
                                    </a>
                                    <a href="https://wa.me/244948242262" target="_blank" class="text-green-600 hover:text-green-800 text-sm">
                                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                    </a>
                                    <a href="mailto:suporte@logigate.ao" class="text-gray-600 hover:text-gray-800 text-sm">
                                        <i class="fas fa-envelope mr-1"></i> Email
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Coluna Direita - Resultados e Informações --}}
                <div class="lg:col-span-1">
                    {{-- Resumo da Compra --}}
                    <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 lg:sticky lg:top-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Resumo da Compra</h2>
                        
                        @php
                            $baseValue = $amount / 1.14;
                            $iva = $amount - $baseValue;
                        @endphp
                        
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Plano:</span>
                                <span class="font-medium">{{ $plano->nome }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Modalidade:</span>
                                <span class="font-medium">{{ ucfirst($cycle) }}</span>
                            </div>
                            
                            <div class="border-t pt-4 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Valor base:</span>
                                    <span class="font-medium">{{ number_format($baseValue, 0, ',', '.') }} AOA</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">IVA (14%):</span>
                                    <span class="font-medium">{{ number_format($iva, 0, ',', '.') }} AOA</span>
                                </div>
                                
                                <div class="border-t pt-4 flex justify-between">
                                    <span class="font-bold text-gray-900">Total:</span>
                                    <span class="font-bold text-xl text-blue-600">
                                        {{ number_format($amount, 0, ',', '.') }} AOA
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Teste Grátis --}}
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-6">
                                <div class="flex items-start">
                                    <i class="fas fa-gift text-green-600 mr-3 mt-1"></i>
                                    <div>
                                        <h4 class="font-bold text-green-900 mb-1">14 Dias de Teste Grátis</h4>
                                        <p class="text-green-700 text-sm">
                                            A primeira cobrança será feita apenas após o período de teste.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Resultados do Pagamento --}}
                    @if($response)
                        <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 lg:sticky fade-in" wire:key="payment-response">
                            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                @if($paymentStatus === 'pending')
                                    <i class="fas fa-clock text-yellow-600 mr-2"></i>Pagamento Pendente
                                @elseif($paymentStatus === 'processing')
                                    <i class="fas fa-sync-alt text-blue-600 mr-2 animate-spin"></i>Processando
                                @elseif($paymentStatus === 'paid')
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>Pagamento Confirmado
                                @elseif($paymentStatus === 'failed')
                                    <i class="fas fa-times-circle text-red-600 mr-2"></i>Pagamento Falhou
                                @endif
                            </h2>
                            
                            @if($response['method'] === 'GPO')
                                {{-- Resultado GPO --}}
                                <div class="space-y-4">
                                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-mobile-alt text-green-600 mr-3"></i>
                                            <div>
                                                <p class="font-medium text-green-900">Pedido enviado para:</p>
                                                <p class="text-green-700">{{ $phone }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                        <h4 class="font-medium text-blue-900 mb-2">Instruções:</h4>
                                        <ol class="text-blue-800 text-sm space-y-2 list-decimal list-inside">
                                            <li>Verifique o SMS no seu telefone</li>
                                            <li>Autorize o pagamento no MCX App</li>
                                            <li>Aguarde confirmação automática</li>
                                        </ol>
                                    </div>
                                    
                                    <div class="text-center">
                                        <div class="inline-flex items-center space-x-2 text-sm text-gray-600">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                            <span>Aguardando confirmação no MCX App...</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Polling para verificar status --}}
                                    <div wire:poll.5s="checkPaymentStatus" class="hidden"></div>
                                </div>
                                
                            @elseif($response['method'] === 'REF')
                                {{-- Resultado REF --}}
                                <div class="space-y-4">
                                    @if($paymentStatus !== 'paid')
                                    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                                        <div class="mb-4">
                                            <p class="text-gray-600 text-sm mb-1">Entidade</p>
                                            <div class="text-2xl font-bold text-purple-700">
                                                {{ $response['reference']['entity'] ?? '12345' }}
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <p class="text-gray-600 text-sm mb-1">Referência</p>
                                            <div class="text-3xl font-bold font-mono text-gray-900 tracking-wider">
                                                {{ isset($response['reference']['reference_number']) ? chunk_split($response['reference']['reference_number'], 3, ' ') : '000 000 000' }}
                                            </div>
                                        </div>
                                        
                                        @if(isset($response['reference']['due_date']))
                                        <div>
                                            <p class="text-gray-600 text-sm mb-1">Expira em:</p>
                                            <div class="text-xl font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($response['reference']['due_date'])->format('d/m/Y') }}
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                        <h4 class="font-medium text-yellow-900 mb-2">Como pagar:</h4>
                                        <ol class="text-yellow-800 text-sm space-y-2 list-decimal list-inside">
                                            <li>Abra o aplicativo Express no seu telemóvel ou vá a um terminal Multicaixa.</li>
                                            <li>Selecione "Pagamentos" > "Pagamento por Referência"</li>
                                            <li>Insira a entidade: <strong>{{ $response['reference']['entity'] ?? '12345' }}</strong></li>
                                            <li>Digite a referência: <strong>{{ $response['reference']['reference_number'] ?? '000 000 000' }}</strong></li>
                                            <li>Confirme o valor de <strong>{{ number_format($amount, 0, ',', '.') }} AOA</strong> e efetue o pagamento</li>
                                        </ol>
                                    </div>
                                    
                                    <div wire:poll.10s="checkPaymentStatus" class="hidden"></div>

                                    {{-- Botões de Ação REF --}}
                                    <div class="flex gap-3 pt-4 border-t">
                                        <button 
                                            onclick="copyToClipboard('{{ $response['reference']['reference_number'] ?? '000000000' }}')" 
                                            class="flex-1 bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition flex items-center justify-center">
                                            <i class="fas fa-copy mr-1"></i> Copiar Referência
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                
                            @elseif($response['method'] === 'TRANSFER')
                                {{-- Resultado Transferência --}}
                                <div class="space-y-4">
                                    @if($paymentStatus !== 'paid')
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                        <h4 class="font-medium text-yellow-900 mb-3">Dados Bancários</h4>
                                        
                                        <div class="space-y-3">
                                            @foreach($response['bank_data'] as $key => $value)
                                                @if(!is_array($value) && $key !== 'instructions')
                                                <div class="flex justify-between items-center">
                                                    <span class="text-yellow-800 text-sm capitalize">{{ $key }}:</span>
                                                    <span class="font-medium">{{ $value }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    @if(isset($response['bank_data']['instructions']) && is_array($response['bank_data']['instructions']))
                                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                        <h4 class="font-medium text-gray-900 mb-2">Instruções:</h4>
                                        <ol class="text-gray-700 text-sm space-y-2 list-decimal list-inside">
                                            @foreach($response['bank_data']['instructions'] as $instruction)
                                                <li>{{ $instruction }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    @endif
                                    
                                    <div wire:poll.15s="checkPaymentStatus" class="hidden"></div>
                                    
                                    {{-- Botões de Ação Transferência --}}
                                    <div class="flex gap-3 pt-4 border-t">
                                        <button 
                                            onclick="copyBankData()" 
                                            class="flex-1 bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700 transition flex items-center justify-center">
                                            <i class="fas fa-copy mr-1"></i> Copiar Dados
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            @endif
                            
                            {{-- Sucesso do Pagamento --}}
                            @if($paymentStatus === 'paid')
                                <div class="mt-6 bg-green-50 border border-green-200 rounded-xl p-6 text-center fade-in">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-check text-green-600 text-2xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-green-700 mb-2">Pagamento Confirmado!</h3>
                                    <p class="text-green-600 mb-4">
                                        Seu plano <strong>{{ $plano->nome }}</strong> foi ativado com sucesso.
                                    </p>
                                    <a href="{{ route('dashboard') }}" 
                                       class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                                        Ir para o Dashboard
                                    </a>
                                </div>
                            @endif
                            
                            {{-- Falha do Pagamento --}}
                            @if($paymentStatus === 'failed')
                                <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-6 text-center fade-in">
                                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-times text-red-600 text-2xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold text-red-700 mb-2">Pagamento Falhou</h3>
                                    <p class="text-red-600 mb-4">
                                        O pagamento não foi processado. Tente novamente ou escolha outro método.
                                    </p>
                                    <button wire:click="$set('response', null)" 
                                            class="inline-block bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition">
                                        Tentar Novamente
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .fade-in {
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        .payment-method {
            transition: all 0.2s ease;
        }
        
        .payment-method:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .payment-method:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copiado para a área de transferência!');
            }).catch(() => {
                alert('Erro ao copiar. Tente novamente.');
            });
        }

        function copyBankData() {
            @if($response && isset($response['bank_data']))
                let bankData = @json($response['bank_data']);
                let text = '';
                
                Object.entries(bankData).forEach(([key, value]) => {
                    if (key !== 'instructions' && !Array.isArray(value)) {
                        text += `${key.charAt(0).toUpperCase() + key.slice(1)}: ${value}\n`;
                    }
                });
                
                copyToClipboard(text);
            @endif
        }

        document.addEventListener('livewire:initialized', function () {
            // Evento de ativação de subscrição
            Livewire.on('subscription-activated', (data) => {
                console.log('Plano ativado:', data.plan);
            });
        });
    </script>
    @endpush
</div>