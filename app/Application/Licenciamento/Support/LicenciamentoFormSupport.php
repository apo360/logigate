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
            'estancias' => Schema::hasTable('estancias') ? Estancia::query()->orderBy('desc_estancia')->get() : collect(),
            'paises' => Schema::hasTable('paises') ? Pais::query()->orderBy('pais')->get() : collect(),
            'portos' => Schema::hasTable('portos') ? Porto::query()->orderBy('porto')->get() : collect(),
            'listaBancos' => BancoListService::getOptions(),
        ];
    }

    public function rules(int $empresaId): array
    {
        return [
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
            'moeda' => ['required', 'string', 'size:3'],
            'tipo_declaracao' => ['required', Rule::in(['11', '21'])],
            'tipo_transporte' => ['required', Rule::in(['1', '2', '3', '4', '5', '6', '7', '8'])],
            'registo_transporte' => ['nullable', 'string', 'max:150'],
            'nacionalidade_transporte' => Schema::hasTable('paises') ? ['nullable', 'integer', 'exists:paises,id'] : ['nullable'],
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
            'pais_origem' => Schema::hasTable('paises') ? ['nullable', 'integer', 'exists:paises,id'] : ['nullable'],
            'porto_origem' => ['nullable', 'string', 'max:100'],
            'Nr_factura' => ['nullable', 'string', 'max:100'],
            'status_fatura' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function calculatedValues(mixed $fobTotal, mixed $frete, mixed $seguro): array
    {
        return [
            'cif' => (float) ($fobTotal ?? 0) + (float) ($frete ?? 0) + (float) ($seguro ?? 0),
        ];
    }

    public function relations(): array
    {
        $relations = ['cliente', 'exportador', 'estancia', 'mercadorias', 'documentosArquivos', 'mercadoriasAgrupadas'];

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $relations = array_values(array_diff($relations, ['cliente']));
        }

        foreach ([
            'estancia' => 'estancias',
            'mercadorias' => 'mercadorias',
            'documentosArquivos' => 'documento_arquivos',
            'mercadoriasAgrupadas' => 'mercadoria_agrupadas',
        ] as $relation => $table) {
            if (!Schema::hasTable($table)) {
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

        if (!Schema::hasColumn('customers', 'deleted_at')) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query->where('empresa_id', $empresa->id)->orderBy('CompanyName')->get();
    }

    private function tenantExportadores(Empresa $empresa): Collection
    {
        if (Schema::hasTable('exportador_empresas')) {
            return $empresa->exportadors()->orderBy('Exportador')->get();
        }

        return Exportador::query()->where('empresa_id', $empresa->id)->orderBy('Exportador')->get();
    }
}
