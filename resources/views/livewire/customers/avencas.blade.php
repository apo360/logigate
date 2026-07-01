<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Avenças</h2>
                <p class="text-sm text-slate-600">{{ $customer->CompanyName }}</p>
            </div>

            @if($canManage)
                <button type="button"
                        wire:click="openCreateForm"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Nova Avença
                </button>
            @else
                <button type="button"
                        disabled
                        class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-500">
                    Nova Avença
                </button>
            @endif
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">Total</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ $totalAvencas }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-slate-500">Ativas</div>
            <div class="mt-1 text-2xl font-bold text-green-700">{{ $avencasAtivas }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:col-span-2">
            <div class="text-sm text-slate-500">Recorrência mensal estimada</div>
            <div class="mt-1 text-2xl font-bold text-blue-700">{{ number_format($valorMensalEstimado, 2, ',', '.') }} Kz</div>
            <div class="mt-1 text-xs text-slate-500">
                Próxima cobrança: {{ $proximaCobranca?->format('d/m/Y') ?? 'não definida' }}
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($showStructuredNotice)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Avenças não geram cobranças nesta fase</p>
            <p class="mt-1">
                Este submódulo permite geração manual e controlada de débito na Conta Corrente. Não são geradas
                facturas externas, pagamentos reais, contratos formais ou cobranças automáticas por job.
            </p>
            @unless($schemaReady)
                <p class="mt-2 font-semibold">
                    O schema actual ainda não possui todos os campos tenant-safe. A criação e edição ficam bloqueadas
                    até a migration evolutiva de avenças ser executada.
                </p>
            @endunless
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Título</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Valor</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Periodicidade</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Início</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Fim</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Cobrança</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Estado</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($avencas as $avenca)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $avenca->titulo_exibicao }}</div>
                                @if($avenca->descricao)
                                    <div class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $avenca->descricao }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format((float) $avenca->valor, 2, ',', '.') }} Kz</td>
                            <td class="px-4 py-3 text-slate-600">{{ ucfirst((string) $avenca->periodicidade) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $avenca->data_inicio?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $avenca->data_fim?->format('d/m/Y') ?? 'Sem fim' }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>Próxima: {{ $avenca->proxima_cobranca_em?->format('d/m/Y') ?? '-' }}</div>
                                <div class="text-xs text-slate-500">
                                    Última: {{ $avenca->ultima_cobranca_em?->format('d/m/Y') ?? '-' }}
                                </div>
                                @if($avenca->ultimo_movimento_id)
                                    <div class="text-xs font-semibold text-blue-700">Mov. #{{ $avenca->ultimo_movimento_id }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClasses = [
                                        'rascunho' => 'bg-slate-100 text-slate-700',
                                        'ativa' => 'bg-green-50 text-green-700',
                                        'suspensa' => 'bg-amber-50 text-amber-700',
                                        'cancelada' => 'bg-red-50 text-red-700',
                                        'encerrada' => 'bg-blue-50 text-blue-700',
                                        'expirada' => 'bg-zinc-100 text-zinc-700',
                                    ];
                                    $estado = $avenca->estado;
                                @endphp
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $statusClasses[$estado] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($canManage)
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button"
                                                wire:click="openEditForm({{ $avenca->id }})"
                                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            Editar
                                        </button>

                                        @if($canGenerateMovimento && $estado === 'ativa')
                                            <button type="button"
                                                    wire:click="gerarMovimento({{ $avenca->id }})"
                                                    class="rounded-lg border border-purple-200 px-3 py-1.5 text-xs font-semibold text-purple-700 hover:bg-purple-50">
                                                Gerar débito
                                            </button>
                                        @endif

                                        @if($estado !== 'ativa')
                                            <button type="button"
                                                    wire:click="changeStatus({{ $avenca->id }}, 'ativa')"
                                                    class="rounded-lg border border-green-200 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-50">
                                                Ativar
                                            </button>
                                        @endif

                                        @if($estado === 'ativa')
                                            <button type="button"
                                                    wire:click="changeStatus({{ $avenca->id }}, 'suspensa')"
                                                    class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                                                Suspender
                                            </button>
                                        @endif

                                        @if(!in_array($estado, ['cancelada', 'encerrada'], true))
                                            <button type="button"
                                                    wire:click="changeStatus({{ $avenca->id }}, 'cancelada')"
                                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                                Cancelar
                                            </button>
                                            <button type="button"
                                                    wire:click="changeStatus({{ $avenca->id }}, 'encerrada')"
                                                    class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                                Encerrar
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Somente leitura</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                <div class="font-semibold text-slate-700">Este cliente ainda não possui avenças.</div>
                                <div class="mt-1">Crie uma avença para acompanhar serviços recorrentes.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">
                        {{ $editingId ? 'Editar Avença' : 'Nova Avença' }}
                    </h3>
                    <button type="button" wire:click="closeForm" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                        Fechar
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Título</label>
                        <input type="text" wire:model="form.titulo" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('form.titulo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Descrição</label>
                        <textarea wire:model="form.descricao" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('form.descricao') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Valor</label>
                            <input type="number" min="0" step="0.01" wire:model="form.valor" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.valor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Periodicidade</label>
                            <select wire:model="form.periodicidade" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="mensal">Mensal</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="semestral">Semestral</option>
                                <option value="anual">Anual</option>
                            </select>
                            @error('form.periodicidade') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Estado</label>
                            <select wire:model="form.status" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="rascunho">Rascunho</option>
                                <option value="ativa">Ativa</option>
                                <option value="suspensa">Suspensa</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="encerrada">Encerrada</option>
                                <option value="expirada">Expirada</option>
                            </select>
                            @error('form.status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Data início</label>
                            <input type="date" wire:model="form.data_inicio" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.data_inicio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Data fim</label>
                            <input type="date" wire:model="form.data_fim" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.data_fim') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Dia cobrança</label>
                            <input type="number" min="1" max="31" wire:model="form.dia_cobranca" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.dia_cobranca') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Observações</label>
                        <textarea wire:model="form.observacoes" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        @error('form.observacoes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button"
                                wire:click="closeForm"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Guardar Avença
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
