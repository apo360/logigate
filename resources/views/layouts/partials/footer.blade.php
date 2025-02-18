<!-- resources/views/layouts/partials/footer.blade.php -->
<footer class="bg-white border-t border-gray-200 py-4">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <!-- Direitos Autorais e Versão -->
            <div class="text-center md:text-left">
                <p class="text-sm text-gray-600">
                    &copy; {{ date('Y') }} Logigate. Todos os direitos reservados.
                </p>
                <p class="text-xs text-gray-500 mt-1">Versão 1.0.0</p>
            </div>

            <!-- Links Rápidos -->
            <div class="flex space-x-6">
                <a href="#" class="text-sm text-gray-600 hover:text-gray-900">Suporte</a>
                <a href="#" class="text-sm text-gray-600 hover:text-gray-900">Termos de Uso</a>
                <a href="#" class="text-sm text-gray-600 hover:text-gray-900">Política de Privacidade</a>
            </div>

            <!-- Ícone de Status -->
            <div x-data="{ status: 'online' }" class="flex items-center space-x-2">
                <span x-bind:class="status === 'online' ? 'bg-green-500' : 'bg-red-500'" class="h-3 w-3 rounded-full"></span>
                <p x-text="status === 'online' ? 'Sistema Online' : 'Sistema Offline'" class="text-sm text-gray-600"></p>
            </div>
        </div>
    </div>
</footer>