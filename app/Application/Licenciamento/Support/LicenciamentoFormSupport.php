<?php

declare(strict_types=1);

namespace App\Application\Licenciamento\Support;

use App\Domains\Banco\Services\BancoListService;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Pais;
use App\Models\Porto;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

final class LicenciamentoFormSupport
{
    public function options(Empresa $empresa): array
    {
        return [
            'clientes' => $this->tenantCustomers($empresa),
            'exportadores' => $this->tenantExportadores($empresa),
            'estancias' => Schema::hasTable('estancias')
                ? Estancia::query()->orderBy('desc_estancia')->get()
                : collect(),
            'paises' => Schema::hasTable('paises')
                ? Pais::query()->orderBy('pais')->get()
                : collect(),
            'portos' => Schema::hasTable('portos')
                ? Porto::query()->orderBy('porto')->get()
                : collect(),
            'listaBancos' => BancoListService::getOptions(),
        ];
    }

    public function rules(int $empresaId, ?int $licenciamentoId = null): array
    {
        return [
            'codigo_licenciamento' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('licenciamentos', 'codigo_licenciamento')
                    ->ignore($licenciamentoId)
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],

            'cliente_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')->where(function ($query) use ($empresaId): void {
                    $query->where(function ($tenantQuery) use ($empresaId): void {
                        $tenantQuery->where('empresa_id', $empresaId);

                        if (Schema::hasTable('customers_empresas')) {
                            $tenantQuery->orWhereExists(function ($pivot) use ($empresaId): void {
                                $pivot->selectRaw('1')
                                    ->from('customers_empresas')
                                    ->whereColumn('customers_empresas.customer_id', 'customers.id')
                                    ->where('customers_empresas.empresa_id', $empresaId);
                            });
                        }
                    });
                }),
            ],

            'exportador_id' => [
                'required',
                'integer',
                Rule::exists('exportadors', 'id')->where(function ($query) use ($empresaId): void {
                    $query->where(function ($tenantQuery) use ($empresaId): void {
                        $tenantQuery->where('empresa_id', $empresaId);

                        if (Schema::hasTable('exportador_empresas')) {
                            $tenantQuery->orWhereExists(function ($pivot) use ($empresaId): void {
                                $pivot->selectRaw('1')
                                    ->from('exportador_empresas')
                                    ->whereColumn('exportador_empresas.exportador_id', 'exportadors.id')
                                    ->where('exportador_empresas.empresa_id', $empresaId);
                            });
                        }
                    });
                }),
            ],

            'estancia_id' => Schema::hasTable('estancias')
                ? ['required', 'integer', 'exists:estancias,id']
                : ['required', 'integer'],

            'referencia_cliente' => ['required', 'string', 'max:50'],
            'factura_proforma' => ['required', 'string', 'max:50'],
            'descricao' => ['required', 'string', 'max:150'],

            'moeda' => [
                'required',
                'string',
                'size:3',
                Rule::in(['AOA', 'USD', 'EUR', 'ZAR', 'CNY']),
            ],

            'tipo_declaracao' => ['required', Rule::in(['11', '21'])],
            'tipo_transporte' => ['required', Rule::in(['1', '2', '3', '4', '5', '6', '7', '8'])],

            'registo_transporte' => ['nullable', 'string', 'max:150'],

            'nacionalidade_transporte' => Schema::hasTable('paises')
                ? ['nullable', 'integer', 'exists:paises,id']
                : ['nullable'],

            'manifesto' => ['nullable', 'string', 'max:30'],
            'data_entrada' => ['nullable', 'date'],
            'porto_entrada' => ['nullable', 'string', 'max:20'],

            'peso_bruto' => ['nullable', 'numeric', 'min:0'],
            'adicoes' => ['nullable', 'integer', 'min:0'],

            'metodo_avaliacao' => ['required', Rule::in(['GATT', 'Outro'])],
            'codigo_volume' => ['required', Rule::in(['B', 'F', 'G', 'L', 'N'])],
            'qntd_volume' => ['required', 'integer', 'min:1'],

            'forma_pagamento' => ['required', 'string', 'max:5'],
            'codigo_banco' => ['nullable', 'string', 'max:10'],

            'fob_total' => ['required', 'numeric', 'min:0'],
            'frete' => ['nullable', 'numeric', 'min:0'],
            'seguro' => ['nullable', 'numeric', 'min:0'],
            'cif' => ['nullable', 'numeric', 'min:0'],

            'pais_origem' => Schema::hasTable('paises')
                ? ['nullable', 'integer', 'exists:paises,id']
                : ['nullable'],

            'porto_origem' => ['nullable', 'string', 'max:100'],
            'Nr_factura' => ['nullable', 'string', 'max:100'],

            'status_fatura' => [
                'nullable',
                Rule::in([
                    'pendente',
                    'emitida',
                    'paga',
                    'anulada',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_licenciamento.unique' => 'Este código de licenciamento já existe para esta empresa.',

            'cliente_id.required' => 'O campo Cliente é obrigatório.',
            'cliente_id.exists' => 'O cliente seleccionado não é válido para a empresa activa.',

            'exportador_id.required' => 'O campo Exportador é obrigatório.',
            'exportador_id.exists' => 'O exportador seleccionado não é válido para a empresa activa.',

            'estancia_id.required' => 'O campo Estância é obrigatório.',
            'estancia_id.exists' => 'A estância seleccionada não é válida.',

            'referencia_cliente.required' => 'A referência do cliente é obrigatória.',
            'factura_proforma.required' => 'A factura proforma é obrigatória.',
            'descricao.required' => 'A descrição é obrigatória.',

            'moeda.required' => 'O campo Moeda é obrigatório.',
            'moeda.size' => 'A moeda deve ter exactamente 3 caracteres.',
            'moeda.in' => 'A moeda seleccionada não é suportada.',

            'tipo_declaracao.required' => 'O tipo de declaração é obrigatório.',
            'tipo_declaracao.in' => 'O tipo de declaração seleccionado não é válido.',

            'tipo_transporte.required' => 'O tipo de transporte é obrigatório.',
            'tipo_transporte.in' => 'O tipo de transporte seleccionado não é válido.',

            'metodo_avaliacao.required' => 'O método de avaliação é obrigatório.',
            'metodo_avaliacao.in' => 'O método de avaliação seleccionado não é válido.',

            'codigo_volume.required' => 'O código do volume é obrigatório.',
            'codigo_volume.in' => 'O código do volume seleccionado não é válido.',

            'qntd_volume.required' => 'A quantidade de volumes é obrigatória.',
            'qntd_volume.integer' => 'A quantidade de volumes deve ser um número inteiro.',
            'qntd_volume.min' => 'A quantidade de volumes deve ser no mínimo 1.',

            'forma_pagamento.required' => 'A forma de pagamento é obrigatória.',

            'fob_total.required' => 'O campo FOB Total é obrigatório.',
            'fob_total.numeric' => 'O FOB Total deve ser numérico.',
            'fob_total.min' => 'O FOB Total não pode ser negativo.',

            'frete.numeric' => 'O Frete deve ser numérico.',
            'frete.min' => 'O Frete não pode ser negativo.',

            'seguro.numeric' => 'O Seguro deve ser numérico.',
            'seguro.min' => 'O Seguro não pode ser negativo.',

            'cif.numeric' => 'O CIF deve ser numérico.',
            'cif.min' => 'O CIF não pode ser negativo.',

            'peso_bruto.numeric' => 'O peso bruto deve ser numérico.',
            'peso_bruto.min' => 'O peso bruto não pode ser negativo.',

            'status_fatura.in' => 'O estado da factura não é válido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo_licenciamento' => 'código do licenciamento',
            'cliente_id' => 'cliente',
            'exportador_id' => 'exportador',
            'estancia_id' => 'estância',
            'referencia_cliente' => 'referência do cliente',
            'factura_proforma' => 'factura proforma',
            'descricao' => 'descrição',
            'moeda' => 'moeda',
            'tipo_declaracao' => 'tipo de declaração',
            'tipo_transporte' => 'tipo de transporte',
            'registo_transporte' => 'registo do transporte',
            'nacionalidade_transporte' => 'nacionalidade do transporte',
            'manifesto' => 'manifesto',
            'data_entrada' => 'data de entrada',
            'porto_entrada' => 'porto de entrada',
            'peso_bruto' => 'peso bruto',
            'adicoes' => 'adições',
            'metodo_avaliacao' => 'método de avaliação',
            'codigo_volume' => 'código do volume',
            'qntd_volume' => 'quantidade de volumes',
            'forma_pagamento' => 'forma de pagamento',
            'codigo_banco' => 'código do banco',
            'fob_total' => 'FOB total',
            'frete' => 'frete',
            'seguro' => 'seguro',
            'cif' => 'CIF',
            'pais_origem' => 'país de origem',
            'porto_origem' => 'porto de origem',
            'Nr_factura' => 'número da factura',
            'status_fatura' => 'estado da factura',
        ];
    }

    public function calculatedValues(mixed $fobTotal, mixed $frete, mixed $seguro): array
    {
        return [
            'cif' => round(
                (float) ($fobTotal ?? 0)
                + (float) ($frete ?? 0)
                + (float) ($seguro ?? 0),
                2
            ),
        ];
    }

    public function relations(): array
    {
        $relations = [
            'cliente',
            'exportador',
            'estancia',
            'mercadorias',
            'documentosArquivos',
            'mercadoriasAgrupadas',
        ];

        if (! Schema::hasColumn('customers', 'deleted_at')) {
            $relations = array_values(array_diff($relations, ['cliente']));
        }

        foreach ([
            'estancia' => 'estancias',
            'mercadorias' => 'mercadorias',
            'documentosArquivos' => 'documento_arquivos',
            'mercadoriasAgrupadas' => 'mercadoria_agrupadas',
        ] as $relation => $table) {
            if (! Schema::hasTable($table)) {
                $relations = array_values(array_diff($relations, [$relation]));
            }
        }

        return $relations;
    }

    private function tenantCustomers(Empresa $empresa): Collection
    {
        if (Schema::hasTable('customers_empresas')) {
            return $empresa->customers()->orderBy('CompanyName')->get();
        }

        $query = Customer::query();

        if (! Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query
            ->where('empresa_id', $empresa->id)
            ->orderBy('CompanyName')
            ->get();
    }

    private function tenantExportadores(Empresa $empresa): Collection
    {
        if (Schema::hasTable('exportador_empresas')) {
            return $empresa->exportadors()->orderBy('Exportador')->get();
        }

        return Exportador::query()
            ->where('empresa_id', $empresa->id)
            ->orderBy('Exportador')
            ->get();
    }
}