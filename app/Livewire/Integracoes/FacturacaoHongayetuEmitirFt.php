<?php

namespace App\Livewire\Integracoes;

use App\Application\FacturacaoIntegracao\Actions\EmitirFacturaExternaAction;
use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaExternaDTO;
use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaLinhaDTO;
use App\Application\Licenciamento\Support\LicenciamentoFormSupport;
use App\Domains\FacturacaoIntegracao\Enums\MetodoPagamentoHongayetuEnum;
use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Domains\FacturacaoIntegracao\Enums\TipoDocumentoHongayetuEnum;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalCustomerMapping;
use App\Models\ExternalInvoice;
use App\Models\Produto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Throwable;

class FacturacaoHongayetuEmitirFt extends Component
{
    public ?Empresa $empresa = null;
    // customer_id, licenciamento_id e processo_id vindo da URL
    public ?int $customerId = null;

    public ?int $licenciamentoId = null;

    public ?int $processoId = null;

    public ?EmpresaIntegracao $integracao = null;

    public array $clientes = [];

    public array $produtosServicos = [];

    public array $estabelecimentos = [];

    public array $MetodosPagamento = [];

    public array $bancos = [];

    public array $TipoDocumento = [];

    public array $form = [
        'tipo_documento' => '',
        'customer_id' => '',
        'external_customer_id' => '',
        'cliente_nome' => '',
        'cliente_nif' => '',
        'cliente_email' => '',
        'cliente_telefone' => '',
        'estabelecimento_id' => '',
        'data_emissao' => '',
        'vencimento_opcao' => '30',
        'data_vencimento_especifica' => '',
        'data_expiracao' => '',
        'nossa_referencia' => '',
        'vossa_referencia' => '',
        'obs' => '',
        'desconto_percentual' => 0,
        'desconto_valor' => 0,
        'confirmarEmissao' => false,
        'linhas' => [],
        'metodo_pagamento' => [
            'tipo' => '',
            'valor' => 0,
            'banco_id' => null,
            'referencia' => null,
            'data_pagamento' => '',
        ],
    ];

    protected $listeners = [
        'clienteCriado' => 'adicionarClienteNaLista',
    ];

    public ?string $customerMappingStatus = null;

    public ?string $successMessage = null;

    public ?string $externalInvoiceNumber = null;

    // Controle de exibição da secção Metodo de pagamento caso seja TPA e Transferência Bancária
    public bool $showMetodoPagamentoSection = false;

    public bool $showProdutoModal = false;

    public ?int $produtoModalIndex = null;

    public string $produtoSearch = '';

    public string $produtoFilter = 'todos';

    public function adicionarClienteNaLista($clienteId, $nome = null)
    {
        $this->clientes = app(LicenciamentoFormSupport::class)->options($this->empresa())['clientes'];
        $this->customerId = $clienteId;
        $this->dispatch('fecharModalCliente');
    }

    // Métodos para abrir os modais
    public function abrirModalCliente()
    {
        $this->dispatch('abrirModalCliente');
    }

    public function mount(?int $customer_id = null, ?int $licenciamento_id = null, ?int $processo_id = null): void
    {
        $this->customerId = $customer_id;
        $this->licenciamentoId = $licenciamento_id;
        $this->processoId = $processo_id;
        $this->empresa = Auth::user()?->empresaAtiva();
        abort_unless($this->empresa, 403);

        $this->integracao = EmpresaIntegracao::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('tipo', TipoIntegracaoEnum::Facturacao->value)
            ->where('provedor', ProvedorIntegracaoEnum::HongayetuFacturacao->value)
            ->where('estado', EstadoIntegracaoEnum::Activo->value)
            ->firstOrFail();

        $this->clientes = $this->loadClientes()->all();
        $this->produtosServicos = $this->loadProdutosServicos()->all();
        $this->form['linhas'] = [$this->emptyLine()];
        $this->form['tipo_documento'] = (string) TipoDocumentoHongayetuEnum::FT->value;
        $this->form['data_emissao'] = now()->format('Y-m-d');
        $this->applyVencimentoOpcao();

        if (request()->filled('customer_id')) {
            $this->form['customer_id'] = (string) request()->integer('customer_id');
            $this->updatedFormCustomerId($this->form['customer_id']);
        }

        $this->MetodosPagamento = MetodoPagamentoHongayetuEnum::cases();
        $this->TipoDocumento = TipoDocumentoHongayetuEnum::cases();

        $this->showMetodoPagamentoSection = false;
    }

    public function updatedFormVencimentoOpcao(): void
    {
        $this->applyVencimentoOpcao();
    }

    public function updatedFormDataVencimentoEspecifica(): void
    {
        if (($this->form['vencimento_opcao'] ?? '') === 'especifica') {
            $this->form['data_expiracao'] = $this->form['data_vencimento_especifica'] ?: '';
        }
    }

    // Update handlers for form fields
    public function updatedFormMetodoPagamentoTipo(mixed $value): void
    {
        $tipo = MetodoPagamentoHongayetuEnum::tryFrom((string) $value);

        if (! $tipo) {
            $this->form['metodo_pagamento']['tipo'] = '';
            $this->showMetodoPagamentoSection = false;
            return;
        }

        $this->form['metodo_pagamento']['tipo'] = $tipo->value;

        // Mostrar a secção de método de pagamento apenas se for TPA ou Transferência Bancária
        $this->showMetodoPagamentoSection = in_array($tipo, [
            MetodoPagamentoHongayetuEnum::TPA,
            MetodoPagamentoHongayetuEnum::TRANSFERENCIA_BANCARIA,
        ], true);
    }

    public function updatedFormCustomerId(mixed $value): void
    {
        $customerId = (int) $value;

        if ($customerId <= 0) {
            $this->form['customer_id'] = '';
            $this->resetCustomerFields();
            return;
        }

        $customer = $this->findCustomer($customerId);

        if (! $customer) {
            $this->form['customer_id'] = '';
            $this->resetCustomerFields();
            return;
        }

        $mapping = $this->mappingFor($customer->id);

        $this->form['customer_id'] = (string) $customer->id;
        $this->form['external_customer_id'] = $mapping?->external_customer_id ? (string) $mapping->external_customer_id : '';
        $this->form['cliente_nome'] = (string) $customer->CompanyName;
        $this->form['cliente_nif'] = (string) $customer->CustomerTaxID;
        $this->form['cliente_email'] = (string) ($customer->Email ?? '');
        $this->form['cliente_telefone'] = (string) ($customer->Telephone ?? '');
        $this->customerMappingStatus = $mapping
            ? 'Cliente já mapeado com Hongayetu.'
            : 'Cliente será enviado por nome/NIF nesta emissão.';
    }

    public function openProdutoModal(int $index): void
    {
        if (! isset($this->form['linhas'][$index])) {
            return;
        }

        $this->produtoModalIndex = $index;
        $this->showProdutoModal = true;
    }

    public function closeProdutoModal(): void
    {
        $this->showProdutoModal = false;
        $this->produtoModalIndex = null;
        $this->produtoSearch = '';
        $this->produtoFilter = 'todos';
    }

    public function setProdutoFilter(string $filter): void
    {
        if (! in_array($filter, ['todos', 'product', 'service'], true)) {
            return;
        }

        $this->produtoFilter = $filter;
    }

    public function selectProdutoFromModal(string $value): void
    {
        if ($this->produtoModalIndex === null) {
            return;
        }

        $this->selectProdutoServico($this->produtoModalIndex, $value);
        $this->closeProdutoModal();
    }

    public function selectProdutoServico(int $index, string $value): void
    {
        $item = collect($this->produtosServicos)->firstWhere('value', $value);

        if (! $item || ! isset($this->form['linhas'][$index])) {
            return;
        }

        $this->form['linhas'][$index]['produto_servico'] = $value;
        $this->form['linhas'][$index]['local_type'] = $item['type'];
        $this->form['linhas'][$index]['local_id'] = (string) $item['id'];
        $this->form['linhas'][$index]['codigo'] = $item['code'] ?? '';
        $this->form['linhas'][$index]['descricao'] = $item['name'];
        $this->form['linhas'][$index]['preco'] = $item['price'];
        $this->form['linhas'][$index]['external_artigo_id'] = $item['external_artigo_id'] ?? '';
    }

    public function addLine(): void
    {
        $this->form['linhas'][] = $this->emptyLine();
        $this->openProdutoModal(array_key_last($this->form['linhas']));
    }

    public function removeLine(int $index): void
    {
        unset($this->form['linhas'][$index]);
        $this->form['linhas'] = array_values($this->form['linhas']);

        if ($this->form['linhas'] === []) {
            $this->addLine();
        }
    }

    public function emitir(EmitirFacturaExternaAction $action): void
    {
        $this->resetErrorBag();
        $this->successMessage = null;
        $this->externalInvoiceNumber = null;

        $validated = $this->validate($this->rules());
        $customer = $this->findCustomer((int) $validated['form']['customer_id']);

        if (! $customer) {
            $this->addError('form.customer_id', 'O cliente seleccionado não pertence à empresa actual.');
            return;
        }

        $mapping = $this->mappingFor($customer->id);

        try {
            $result = $action->execute(new EmitirFacturaExternaDTO(
                empresaId: $this->empresa->id,
                empresaIntegracaoId: $this->integracao->id,
                customerId: $customer->id,
                externalCustomerId: $mapping?->external_customer_id,
                documentType: ExternalInvoice::DOC_TYPE_FT,
                apiTipo: ExternalInvoice::API_TIPO_FT,
                moeda: 0,
                currency: 'AOA',
                clienteNome: $validated['form']['cliente_nome'] ?: $customer->CompanyName,
                clienteNif: $validated['form']['cliente_nif'] ?: $customer->CustomerTaxID,
                clienteEmail: $validated['form']['cliente_email'] ?: $customer->Email,
                clienteTelefone: $validated['form']['cliente_telefone'] ?: $customer->Telephone,
                estabelecimentoId: filled($validated['form']['estabelecimento_id']) ? (int) $validated['form']['estabelecimento_id'] : null,
                dueDate: $validated['form']['data_expiracao'] ?: null,
                localReference: $validated['form']['nossa_referencia'] ?: null,
                nossaReferencia: $validated['form']['nossa_referencia'] ?: null,
                vossaReferencia: $validated['form']['vossa_referencia'] ?: null,
                obs: $validated['form']['obs'] ?: null,
                linhas: $this->lineDtos($validated['form']['linhas']),
            ));

            $this->successMessage = 'Factura Comercial emitida com sucesso.';
            $this->externalInvoiceNumber = $result->externalInvoiceNumber;
            $this->dispatch('toast', type: 'success', message: $this->successMessage);
        } catch (Throwable $exception) {
            $this->addError('emitir', mb_substr($exception->getMessage(), 0, 1000));
        }
    }

    public function render()
    {
        return view('livewire.integracoes.facturacao-hongayetu-emitir-ft', [
            'produtosFiltrados' => $this->produtosFiltrados(),
            'subtotal' => $this->subtotal(),
            'descontoGlobal' => $this->descontoGlobal(),
            'totalGeral' => $this->totalGeral(),
        ]);
    }

    private function rules(): array
    {
        return [
            'form.customer_id' => ['required', 'integer'],
            'form.external_customer_id' => ['nullable', 'integer'],
            'form.cliente_nome' => ['required', 'string', 'max:255'],
            'form.cliente_nif' => ['nullable', 'string', 'max:50'],
            'form.cliente_email' => ['nullable', 'email', 'max:255'],
            'form.cliente_telefone' => ['nullable', 'string', 'max:80'],
            'form.estabelecimento_id' => ['nullable', 'integer', 'min:1'],
            'form.data_emissao' => ['nullable', 'date'],
            'form.vencimento_opcao' => ['nullable', 'string', 'max:20'],
            'form.data_vencimento_especifica' => ['nullable', 'date'],
            'form.data_expiracao' => ['nullable', 'date'],
            'form.nossa_referencia' => ['nullable', 'string', 'max:100'],
            'form.vossa_referencia' => ['nullable', 'string', 'max:100'],
            'form.obs' => ['nullable', 'string', 'max:1000'],
            'form.desconto_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'form.desconto_valor' => ['nullable', 'numeric', 'min:0'],
            'form.confirmarEmissao' => ['accepted'],
            'form.linhas' => ['required', 'array', 'min:1'],
            'form.linhas.*.produto_servico' => ['nullable', 'string', 'max:80'],
            'form.linhas.*.local_type' => ['nullable', 'string', 'max:30'],
            'form.linhas.*.local_id' => ['nullable', 'integer'],
            'form.linhas.*.external_artigo_id' => ['required', 'integer', 'min:1'],
            'form.linhas.*.descricao' => ['required', 'string', 'max:255'],
            'form.linhas.*.quantidade' => ['required', 'numeric', 'min:0.0001'],
            'form.linhas.*.preco' => ['required', 'numeric', 'min:0'],
            'form.linhas.*.desconto' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function loadClientes(): Collection
    {
        return Customer::query()
            ->forEmpresa($this->empresa->id)
            ->orderBy('CompanyName')
            ->limit(300)
            ->get(['id', 'CustomerID', 'CompanyName', 'CustomerTaxID', 'Email', 'Telephone'])
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->CompanyName,
                'nif' => $customer->CustomerTaxID,
                'email' => $customer->Email,
                'telephone' => $customer->Telephone,
            ]);
    }

    private function loadProdutosServicos(): Collection
    {
        return Produto::query()
            ->with('price')
            ->where('empresa_id', $this->empresa->id)
            ->orderBy('ProductDescription')
            ->limit(300)
            ->get()
            ->map(function (Produto $produto) {
                $type = $this->produtoType($produto);

                return [
                    'value' => 'product:' . $produto->id,
                    'type' => $type,
                    'id' => $produto->id,
                    'code' => $produto->ProductCode ?: $produto->ProductNumberCode,
                    'name' => $produto->ProductDescription,
                    'price' => (float) ($produto->price?->venda ?? 0),
                    'category' => $type === 'service' ? 'Serviço' : 'Produto',
                    'external_artigo_id' => is_numeric($produto->ProductNumberCode) ? (string) $produto->ProductNumberCode : '',
                ];
            });
    }

    private function findCustomer(int $customerId): ?Customer
    {
        if ($customerId <= 0) {
            return null;
        }

        return Customer::query()
            ->forEmpresa($this->empresa->id)
            ->whereKey($customerId)
            ->first();
    }

    private function mappingFor(int $customerId): ?ExternalCustomerMapping
    {
        return ExternalCustomerMapping::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('customer_id', $customerId)
            ->where(function ($query): void {
                $query->where('provider', ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO)
                    ->orWhereNull('provider');
            })
            ->first();
    }

    /**
     * @param array<int, array<string, mixed>> $lines
     * @return array<int, EmitirFacturaLinhaDTO>
     */
    private function lineDtos(array $lines): array
    {
        return collect($lines)
            ->map(fn (array $line) => new EmitirFacturaLinhaDTO(
                externalArtigoId: (int) $line['external_artigo_id'],
                description: (string) $line['descricao'],
                quantity: (float) $line['quantidade'],
                unitPrice: (float) $line['preco'],
                discountAmount: (float) ($line['desconto'] ?? 0),
                taxPercentage: 0,
                productId: ($line['local_type'] ?? null) === 'product' ? (int) ($line['local_id'] ?? 0) ?: null : null,
                type: ($line['local_type'] ?? null) === 'product' ? 'product' : 'service',
            ))
            ->all();
    }

    private function emptyLine(): array
    {
        return [
            'produto_servico' => '',
            'local_type' => '',
            'local_id' => '',
            'codigo' => '',
            'external_artigo_id' => '',
            'descricao' => '',
            'quantidade' => 1,
            'preco' => 0,
            'desconto' => 0,
        ];
    }

    private function resetCustomerFields(): void
    {
        $this->form['external_customer_id'] = '';
        $this->form['cliente_nome'] = '';
        $this->form['cliente_nif'] = '';
        $this->form['cliente_email'] = '';
        $this->form['cliente_telefone'] = '';
        $this->customerMappingStatus = null;
    }

    private function applyVencimentoOpcao(): void
    {
        $option = (string) ($this->form['vencimento_opcao'] ?? '30');

        if ($option === 'especifica') {
            $this->form['data_expiracao'] = $this->form['data_vencimento_especifica'] ?: '';
            return;
        }

        if (! is_numeric($option)) {
            $this->form['data_expiracao'] = '';
            return;
        }

        $this->form['data_expiracao'] = now()
            ->addDays((int) $option)
            ->format('Y-m-d');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function produtosFiltrados(): array
    {
        $search = str($this->produtoSearch)->lower()->trim()->toString();

        return collect($this->produtosServicos)
            ->when($this->produtoFilter !== 'todos', fn ($items) => $items->where('type', $this->produtoFilter))
            ->filter(function (array $item) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower((string) ($item['name'] ?? '')), $search)
                    || str_contains(strtolower((string) ($item['code'] ?? '')), $search)
                    || str_contains(strtolower((string) ($item['external_artigo_id'] ?? '')), $search);
            })
            ->values()
            ->all();
    }

    public function lineSubtotal(array $line): float
    {
        $quantity = max(0, (float) ($line['quantidade'] ?? 0));
        $price = max(0, (float) ($line['preco'] ?? 0));
        $discount = max(0, (float) ($line['desconto'] ?? 0));

        return max(0, ($quantity * $price) - $discount);
    }

    public function subtotal(): float
    {
        return collect($this->form['linhas'])
            ->sum(fn (array $line) => $this->lineSubtotal($line));
    }

    public function descontoGlobal(): float
    {
        $subtotal = $this->subtotal();
        $percent = min(100, max(0, (float) ($this->form['desconto_percentual'] ?? 0)));
        $value = max(0, (float) ($this->form['desconto_valor'] ?? 0));

        return min($subtotal, (($subtotal * $percent) / 100) + $value);
    }

    public function totalGeral(): float
    {
        return max(0, $this->subtotal() - $this->descontoGlobal());
    }

    private function produtoType(Produto $produto): string
    {
        $type = str((string) $produto->ProductType)->lower()->trim()->toString();

        return in_array($type, ['s', 'servico', 'serviço', 'service'], true) ? 'service' : 'product';
    }
}
