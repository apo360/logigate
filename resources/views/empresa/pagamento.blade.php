<x-app-layout>
    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-logigate-primary to-logigate-secondary p-8 text-center">
                <h1 class="text-3xl font-bold text-white">Referência de Pagamento</h1>
                <p class="text-white/80 mt-2">Utilize esta referência para efetuar o pagamento</p>
            </div>
            
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="text-5xl font-bold text-gray-900 mb-2 tracking-wider">
                        {{ $pagamento->referencia }}
                    </div>
                    <div class="text-gray-600">Referência Multicaixa</div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-500">Valor a Pagar</div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ number_format($pagamento->valor, 2) }} Kz
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-500">Válida até</div>
                        <div class="text-xl font-bold text-gray-900">
                            {{ $pagamento->data_expiracao_referencia->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Instruções de Pagamento</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Utilize a referência acima em qualquer terminal Multicaixa</li>
                                    <li>O pagamento será processado em até 24 horas</li>
                                    <li>Após o pagamento, os serviços serão ativados automaticamente</li>
                                    <li>Guarde o comprovativo de pagamento</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="window.print()" 
                            class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-50">
                        <i class="fas fa-print mr-2"></i> Imprimir
                    </button>
                    
                    <a href="{{ route('dashboard') }}"
                       class="bg-logigate-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-logigate-dark text-center">
                        <i class="fas fa-home mr-2"></i> Voltar ao Painel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
