<?php

declare(strict_types=1);

namespace App\Application\Processo\Actions;

use App\Application\Processo\DTOs\AsycudaImportPreview;
use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Domains\Processo\Enums\FormaPagamentoEnum;
use App\Domains\PautaAduaneira\ValueObjects\CodigoPautal;
use App\Domains\Banco\Services\BancoListService;
use App\Enums\MoedaEnum;
use App\Infrastructure\Integrations\Asycuda\AsycudaProcessoImportData;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\RegiaoAduaneira;
use App\Models\TipoTransporte;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class PrepararImportacaoAsycudaAction
{
    public function __construct(
        private readonly ProcessoTenantAccessService $tenantAccess,
    ) {
    }

    public function execute(AsycudaProcessoImportData|array $data, array $choices = []): AsycudaImportPreview
    {
        $mapped = $data instanceof AsycudaProcessoImportData ? get_object_vars($data) : $data;
        $mapped = self::arrays($mapped);
        $user = Auth::user();
        if (! $user instanceof User) {
            throw new AuthorizationException('É necessário iniciar sessão para preparar a importação.');
        }
        $empresaId = $this->tenantAccess->empresaIdFor($user);
        if (! $empresaId || ! $this->tenantAccess->canCreateForEmpresa($user, $empresaId)) {
            throw new AuthorizationException('Não foi possível determinar a empresa activa.');
        }
        $empresa = Empresa::query()->findOrFail($empresaId);
        $resolved = ['empresa_id' => (int) $empresa->id];
        $required = [];
        $warnings = $mapped['warnings'] ?? [];
        $blocking = [];
        $references = [];

        $declarant = $mapped['declarantReference'] ?? [];
        $declarantEmail = data_get($mapped, 'unmapped.root.declarantContactDetails.email');
        $declarantDiff = $this->declarantDifferences($declarant, $empresa, is_string($declarantEmail) ? $declarantEmail : null);
        if ($declarantDiff !== []) {
            $warnings[] = 'Os dados do declarante diferem da empresa activa: ' . implode(', ', $declarantDiff) . '.';
        }

        $customerReference = $mapped['customerReference'] ?? [];
        $customerQuery = Customer::query()->forEmpresa($empresaId);
        $customerCode = trim((string) ($customerReference['identifier'] ?? ''));
        $customerMatches = $customerCode === '' ? collect() : (clone $customerQuery)->where('CustomerTaxID', $customerCode)->limit(3)->get(['id', 'CompanyName', 'CustomerTaxID']);
        if (! empty($choices['customer_id'])) $customerMatches = $customerMatches->merge((clone $customerQuery)->whereKey($choices['customer_id'])->get(['id']));
        $references['customer'] = [
            'external' => $customerReference,
            'options' => Customer::query()->forEmpresa($empresaId)->orderBy('CompanyName')->limit(250)->get(['id', 'CompanyName', 'CustomerTaxID'])->map(fn ($m) => ['id' => $m->id, 'name' => $m->CompanyName, 'code' => $m->CustomerTaxID])->all(),
        ];
        $this->resolveOne('customer_id', $customerMatches, $choices['customer_id'] ?? null, $resolved, $required, 'Cliente ASYCUDA sem correspondência única na empresa activa.');

        $exporterReference = $mapped['exportadorReference'] ?? [];
        $exporterQuery = Exportador::query()->where(function ($q) use ($empresaId) {
            $q->where('exportadors.empresa_id', $empresaId)->orWhereHas('empresas', fn ($pivot) => $pivot->where('empresas.id', $empresaId));
        });
        $exporterCode = trim((string) ($exporterReference['identifier'] ?? ''));
        $exporterMatches = $exporterCode === '' ? collect() : (clone $exporterQuery)->where('ExportadorTaxID', $exporterCode)->limit(3)->get(['id', 'Exportador', 'ExportadorTaxID']);
        if (! empty($choices['exportador_id'])) $exporterMatches = $exporterMatches->merge((clone $exporterQuery)->whereKey($choices['exportador_id'])->get(['id']));
        $references['exporter'] = [
            'external' => $exporterReference,
            'options' => (clone $exporterQuery)->orderBy('Exportador')->limit(250)->get(['id', 'Exportador', 'ExportadorTaxID'])->map(fn ($m) => ['id' => $m->id, 'name' => $m->Exportador, 'code' => $m->ExportadorTaxID])->all(),
        ];
        $this->resolveOne('exportador_id', $exporterMatches, $choices['exportador_id'] ?? null, $resolved, $required, 'Exportador não resolvido; seleccione um exportador da empresa activa.');

        $proc = $mapped['processo']['regiao_aduaneira_reference'] ?? [];
        $procedureQuery = RegiaoAduaneira::query()->where('abrev', $proc['abrev'] ?? null)->where('codigo', $proc['codigo'] ?? null);
        if (! empty($choices['tipo_processo_id'])) $procedureQuery = RegiaoAduaneira::query()->whereKey($choices['tipo_processo_id']);
        $references['procedure'] = ['external' => $proc, 'options' => RegiaoAduaneira::query()->orderBy('descricao')->get(['id', 'abrev', 'codigo', 'descricao'])->toArray()];
        $this->resolveOne('tipo_processo_id', $procedureQuery->limit(3)->get(['id']), $choices['tipo_processo_id'] ?? null, $resolved, $required, 'Procedimento sem correspondência única.');

        $officeCode = $mapped['processo']['estancia_reference'] ?? null;
        $references['estancia'] = ['external' => $officeCode, 'options' => Estancia::query()->orderBy('desc_estancia')->get(['id', 'cod_estancia', 'desc_estancia'])->toArray()];
        $estanciaQuery = $officeCode === null ? Estancia::query()->whereRaw('1=0') : Estancia::query()->where('cod_estancia', $officeCode);
        if (! empty($choices['estancia_id'])) $estanciaQuery = Estancia::query()->whereKey($choices['estancia_id']);
        $this->resolveOne('estancia_id', $estanciaQuery->limit(3)->get(['id']), $choices['estancia_id'] ?? null, $resolved, $required, 'Estância de despacho não resolvida.');

        $transportCode = $mapped['processo']['tipo_transporte'] ?? null;
        $transportMatches = is_numeric($transportCode) ? TipoTransporte::query()->whereKey((int) $transportCode)->get(['id']) : collect();
        if (! empty($choices['tipo_transporte_id'])) $transportMatches = TipoTransporte::query()->whereKey($choices['tipo_transporte_id'])->get(['id']);
        $references['transport'] = ['external' => $transportCode, 'options' => TipoTransporte::query()->orderBy('id')->get(['id', 'descricao'])->toArray()];
        $this->resolveOne('tipo_transporte_id', $transportMatches, $choices['tipo_transporte_id'] ?? null, $resolved, $required, 'Modo de transporte sem correspondência.');

        $references['countries'] = [];
        foreach (['origem' => 'Pais_origem', 'destino' => 'Pais_destino', 'nacionalidade_transporte' => 'nacionalidade_transporte'] as $kind => $target) {
            $code = $mapped['processo']['paises'][$kind] ?? null;
            $choiceKey = 'country_' . $kind;
            $matches = $code === null ? collect() : Pais::query()->where('codigo', $code)->limit(3)->get(['id']);
            if (! empty($choices[$choiceKey])) $matches = Pais::query()->whereKey($choices[$choiceKey])->get(['id']);
            $references['countries'][$kind] = ['external' => $code, 'options' => Pais::query()->orderBy('pais')->get(['id', 'codigo', 'pais'])->toArray()];
            if ($code !== null) {
                $this->resolveOne($target, $matches, $choices[$choiceKey] ?? null, $resolved, $required, 'País ' . $kind . ' não resolvido.', false);
            }
        }

        $references['items'] = [];
        foreach ($mapped['mercadorias'] ?? [] as $index => $item) {
            $code = (string) ($item['codigo_aduaneiro'] ?? '');
            $pautas = $this->findPautas($code);
            $chosen = $choices['pauta_ids'][$index] ?? null;
            if ($chosen !== null) $pautas = $pautas->merge(PautaAduaneira::query()->whereKey($chosen)->get(['id', 'codigo', 'descricao']))->unique('id')->values();
            $references['items'][$index] = [
                'pauta_options' => $pautas->map(fn ($p) => ['id' => $p->id, 'codigo' => $p->codigo, 'descricao' => $p->descricao])->all(),
            ];
            if ($chosen !== null && $pautas->contains(fn ($p) => (int) $p->id === (int) $chosen)) {
                $resolved['pautas'][$index] = (int) $chosen;
            } elseif ($pautas->count() === 1) {
                $resolved['pautas'][$index] = (int) $pautas->first()->id;
            } else {
                $required[] = 'pauta_ids.' . $index;
            }

            $internalUnitPrice = $choices['item_prices'][$index] ?? null;
            if ($internalUnitPrice === null || $internalUnitPrice === '') {
                $required[] = 'item_prices.' . $index;
            } elseif (! is_numeric($internalUnitPrice) || (float) $internalUnitPrice < 0) {
                $blocking[] = 'O preço unitário interno confirmado para o item ' . ($index + 1) . ' deve ser numérico e não negativo.';
            } else {
                $resolved['item_prices'][$index] = (float) $internalUnitPrice;
            }
        }

        $bankExternal = $mapped['processo']['bank_reference'] ?? null;
        $bankMatches = collect(BancoListService::getAll())->filter(fn ($bank) => strcasecmp((string) $bank['sname'], (string) $bankExternal) === 0)->values();
        $references['bank'] = ['external' => $bankExternal, 'options' => array_values(BancoListService::getAll())];
        if (isset($choices['codigo_banco']) && array_key_exists((string) $choices['codigo_banco'], BancoListService::getOptions())) {
            $resolved['codigo_banco'] = (string) $choices['codigo_banco'];
        } elseif ($bankMatches->count() === 1) {
            $resolved['codigo_banco'] = (string) $bankMatches->first()['code'];
        } else {
            $required[] = 'codigo_banco';
        }

        $payment = $choices['forma_pagamento'] ?? null;
        if ($payment && FormaPagamentoEnum::tryFrom((string) $payment)) {
            $resolved['forma_pagamento'] = (string) $payment;
        } else {
            $required[] = 'forma_pagamento';
        }

        $currencies = $this->currencies($mapped);
        $references['currencies'] = $currencies;
        if (count($currencies) === 1 && MoedaEnum::tryFrom($currencies[0])) {
            $resolved['moeda'] = $currencies[0];
        } elseif (isset($choices['moeda']) && MoedaEnum::tryFrom((string) $choices['moeda'])) {
            $resolved['moeda'] = (string) $choices['moeda'];
        } else {
            $warnings[] = 'Moeda ausente ou divergente; escolha manualmente se for necessária no Processo.';
        }

        foreach (['fob_total', 'frete', 'seguro', 'cif', 'ValorTotal', 'ValorAduaneiro', 'Cambio'] as $field) {
            $value = $choices['financial'][$field] ?? null;
            if ($value === null || $value === '') continue;
            if (! is_numeric($value) || (float) $value < 0) {
                $blocking[] = 'O valor interno confirmado para ' . $field . ' deve ser numérico e não negativo.';
                continue;
            }
            $resolved['financial_internal'][$field] = (float) $value;
        }

        foreach ($mapped['contentorMercadorias'] ?? [] as $link) {
            if (! empty($link['unresolved'])) {
                $blocking[] = 'A ligação de um contentor referencia um UUID de mercadoria inexistente.';
            }
        }
        $seenLinks = [];
        foreach ($mapped['contentorMercadorias'] ?? [] as $link) {
            $key = ($link['contentor_external_id'] ?? '') . '|' . ($link['mercadoria_external_id'] ?? '');
            if (isset($seenLinks[$key])) $blocking[] = 'Existe uma associação contentor/mercadoria duplicada no ficheiro.';
            $seenLinks[$key] = true;
        }
        if (count(array_filter(array_column($mapped['mercadorias'] ?? [], 'external_id'))) !== count(array_unique(array_filter(array_column($mapped['mercadorias'] ?? [], 'external_id'))))) {
            $blocking[] = 'O ficheiro contém UUIDs externos de mercadoria repetidos.';
        }
        foreach ([['mercadorias', 'external_id', 'mercadoria'], ['contentores', 'external_id', 'contentor']] as [$collection, $field, $label]) {
            foreach ($mapped[$collection] ?? [] as $index => $record) {
                $externalId = $record[$field] ?? null;
                if ($externalId !== null && ! \Illuminate\Support\Str::isUuid((string) $externalId)) {
                    $blocking[] = 'O UUID externo do(a) ' . $label . ' na posição ' . ($index + 1) . ' não é válido.';
                }
            }
        }
        $containerNumbers = [];
        foreach ($mapped['contentores'] ?? [] as $container) {
            if (empty($container['numero'])) $blocking[] = 'Um contentor não tem número obrigatório.';
            $number = mb_strtolower(trim((string) ($container['numero'] ?? '')));
            if ($number !== '' && isset($containerNumbers[$number])) $blocking[] = 'O ficheiro contém números de contentor duplicados.';
            if ($number !== '') $containerNumbers[$number] = true;
        }
        foreach ($mapped['mercadorias'] ?? [] as $item) {
            $code = (string) ($item['codigo_aduaneiro'] ?? '');
            if ((new \App\Application\Mercadoria\Services\MercadoriaRules())->isVehicleCode($code)) {
                $blocking[] = 'Um item classificado como veículo exige dados de marca, modelo e chassis que não estão disponíveis no ficheiro.';
            } elseif ((new \App\Application\Mercadoria\Services\MercadoriaRules())->isMachineCode($code)) {
                $blocking[] = 'Um item classificado como máquina exige potência que não está disponível no ficheiro.';
            }
        }

        if (! empty($resolved['customer_id']) && ! empty($mapped['processo']['registo_transporte'])) {
            $exists = DB::table('processos')->where('empresa_id', $empresaId)->where('customer_id', $resolved['customer_id'])->where('registo_transporte', $mapped['processo']['registo_transporte'])->exists();
            if ($exists) $warnings[] = 'Já existe um processo desta empresa com o mesmo cliente e manifesto; a importação continuará como novo processo.';
        }
        $warnings = array_values(array_unique($warnings));
        if (($mapped['documentos'] ?? []) !== []) $warnings[] = 'A persistência de documentos está adiada; apenas metadados são apresentados.';

        return new AsycudaImportPreview($mapped, $resolved, array_values(array_unique($required)), $warnings, $references, $mapped['unmapped'] ?? [], array_values(array_unique($blocking)));
    }

    private function resolveOne(string $key, iterable $matches, mixed $choice, array &$resolved, array &$required, string $message, bool $mustResolve = true): void
    {
        $matches = collect($matches);
        $ids = $matches->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($choice !== null && in_array((int) $choice, $ids, true)) {
            $resolved[$key] = (int) $choice;
        } elseif (count($ids) === 1) {
            $resolved[$key] = $ids[0];
        } elseif ($mustResolve) {
            $required[] = $key;
        }
    }

    private function findPautas(string $code)
    {
        if ($code === '') return collect();
        try { $normalized = (new CodigoPautal($code))->normalized(); } catch (\Throwable) { return collect(); }
        $matches = PautaAduaneira::query()->where(function ($q) use ($normalized, $code) {
            $q->where('codigo', $code)->orWhereRaw("REPLACE(codigo, '.', '') = ?", [$normalized]);
        })->limit(20)->get(['id', 'codigo', 'descricao']);
        if ($matches->isNotEmpty()) return $matches;
        return PautaAduaneira::query()->where('codigo', 'like', substr($normalized, 0, 4) . '%')->orderBy('codigo')->limit(50)->get(['id', 'codigo', 'descricao']);
    }

    private function currencies(array $mapped): array
    {
        $values = [];
        $walk = function (mixed $value) use (&$walk, &$values): void {
            if (! is_array($value)) return;
            foreach ($value as $key => $child) {
                if ($key === 'currencyCode' && is_string($child) && $child !== '') $values[] = $child;
                else $walk($child);
            }
        };
        $walk($mapped['financial'] ?? []);
        $walk($mapped['mercadorias'] ?? []);
        $values = array_values(array_unique($values));
        sort($values);
        return $values;
    }

    private function declarantDifferences(array $declarant, Empresa $empresa, ?string $externalEmail): array
    {
        $checks = [
            'operatorCode' => ['NIF', $empresa->NIF],
            'name' => ['Empresa', $empresa->Empresa],
            'address' => ['Endereco_completo', $empresa->Endereco_completo],
            'raw.authorizationCode' => ['Cedula', $empresa->Cedula],
        ];
        $diff = [];
        foreach ($checks as $external => [$label, $internal]) {
            $value = trim((string) data_get($declarant, $external, ''));
            if ($value !== '' && trim((string) $internal) !== '' && mb_strtolower($value) !== mb_strtolower(trim((string) $internal))) $diff[] = $label;
        }
        $externalEmail = trim((string) $externalEmail);
        if ($externalEmail !== '' && trim((string) $empresa->Email) !== '' && mb_strtolower($externalEmail) !== mb_strtolower(trim((string) $empresa->Email))) $diff[] = 'Email';
        return $diff;
    }

    private static function arrays(mixed $value): mixed
    {
        if (is_object($value)) $value = get_object_vars($value);
        if (is_array($value)) foreach ($value as $key => $child) $value[$key] = self::arrays($child);
        return $value;
    }
}
