<x-app-layout>
    <div class="container">
        <h2>Criar Avença para Cliente</h2>

        <!-- Formulário de criação da avença -->
        <form action="{{ route('avenca.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="customer_id">Cliente</label>
                <select name="customer_id" id="customer_id" class="form-control" required>
                    <option value="">Selecione o cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="valor">Valor da Avença</label>
                <input type="number" name="valor" id="valor" class="form-control" step="0.01" required>
                <!-- Comentário abaixo do campo "Valor da Avença" -->
                <small class="form-text text-muted">Insira o valor acordado para a avença.</small>
            </div>

            <div class="form-group">
                <label for="data_inicio">Data de Início</label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="periodicidade">Periodicidade</label>
                <select name="periodicidade" id="periodicidade" class="form-control" required>
                    <option value="" selected disabled>Selecione a periodicidade</option>
                    <option value="mensal">Mensal</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="semestral">Semestral</option>
                    <option value="anual">Anual</option>
                </select>
            </div>

            <div class="form-group">
                <label for="data_fim">Data de Fim</label>
                <input type="date" name="data_fim" id="data_fim" class="form-control">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="ativo" id="ativo" class="form-check-input" checked>
                <label for="ativo" class="form-check-label">Ativo</label>
            </div>

            <button type="submit" class="btn btn-primary">Criar Avença</button>
        </form>
    </div>

    <script>
        const dataInicioInput = document.getElementById('data_inicio');
        const periodicidadeInput = document.getElementById('periodicidade');
        const dataFimInput = document.getElementById('data_fim');

        // Função para calcular a data de fim com base na periodicidade
        function calcularDataFim() {
            const dataInicio = new Date(dataInicioInput.value);
            let dataFim = new Date(dataInicio);

            // Verifica a periodicidade selecionada e ajusta a data de fim
            switch (periodicidadeInput.value) {
                case 'mensal':
                    dataFim.setMonth(dataFim.getMonth() + 1);
                    break;
                case 'trimestral':
                    dataFim.setMonth(dataFim.getMonth() + 3);
                    break;
                case 'semestral':
                    dataFim.setMonth(dataFim.getMonth() + 6);
                    break;
                case 'anual':
                    dataFim.setFullYear(dataFim.getFullYear() + 1);
                    break;
                default:
                    dataFim = null;
            }

            // Formatar a data de fim no formato YYYY-MM-DD para o input
            if (dataFim) {
                const year = dataFim.getFullYear();
                const month = String(dataFim.getMonth() + 1).padStart(2, '0');
                const day = String(dataFim.getDate()).padStart(2, '0');
                dataFimInput.value = `${year}-${month}-${day}`;
            }
        }

        // Ouvir mudanças na data de início e na periodicidade
        dataInicioInput.addEventListener('change', calcularDataFim);
        periodicidadeInput.addEventListener('change', calcularDataFim);

        // Verificar se o usuário alterou a data de fim manualmente
        dataFimInput.addEventListener('change', function () {
            const dataInicio = new Date(dataInicioInput.value);
            const dataFim = new Date(dataFimInput.value);
            const diffInMonths = (dataFim.getFullYear() - dataInicio.getFullYear()) * 12 + (dataFim.getMonth() - dataInicio.getMonth());

            let periodicidadeEmMeses;
            switch (periodicidadeInput.value) {
                case 'mensal':
                    periodicidadeEmMeses = 1;
                    break;
                case 'trimestral':
                    periodicidadeEmMeses = 3;
                    break;
                case 'semestral':
                    periodicidadeEmMeses = 6;
                    break;
                case 'anual':
                    periodicidadeEmMeses = 12;
                    break;
                default:
                    periodicidadeEmMeses = 0;
            }

            if (diffInMonths !== periodicidadeEmMeses) {
                const confirmar = confirm('A data de fim não coincide com a periodicidade selecionada. Deseja alterar mesmo assim?');
                if (!confirmar) {
                    calcularDataFim(); // Reverte a alteração e recalcula a data de fim
                }
            }
        });
    </script>
</x-app-layout>