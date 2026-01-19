<div class="bg-white rounded-xl p-3 space-y-3 shadow">
    {{-- FILTRO POR ANO --}}
    <div class="flex justify-end">
        <select wire:model="year"
                class="border rounded-lg px-3 py-2 text-sm">
            @for ($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>
    </div>

    {{-- CHART --}}
    <div class="h-72">
        <canvas id="atividadePorMes"></canvas>
    </div>

    {{-- SCRIPTS --}}

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                const ctx = document.getElementById('atividadePorMes').getContext('2d');

                let chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($labels),
                        datasets: [
                            {
                                label: 'Processos',
                                data: @json($processos),
                                backgroundColor: '#2563eb',
                                stack: 'total'
                            },
                            {
                                label: 'Licenciamentos',
                                data: @json($licenciamentos),
                                backgroundColor: '#16a34a',
                                stack: 'total'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true }
                        }
                    }
                });

                Livewire.on('chart-updated', payload => {
                    chart.data.labels = payload.labels;
                    chart.data.datasets[0].data = payload.processos;
                    chart.data.datasets[1].data = payload.licenciamentos;
                    chart.update();
                });
            });
        </script>
    @endpush
</div>
