<?php

namespace App\Livewire\Processos;

use App\Http\Controllers\IbanController;
use Livewire\Component;
use App\Models\Processo;
use App\Models\ProcessosDraft;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class Form extends Component
{
    /** @var Processo|null */
    public $processo = null;
    public ?int $processoId = null;

    /** @var string create|edit */
    public $mode = 'create';
    public $schema = [];
    public $form = [];
    public array $banks = [];

    public $listeners = [
        'cliente-created' => 'onClienteCreated',
        'exportador-created' => 'onExportadorCreated',
        'loadDraft' => 'loadDraft',
        'selectSearchUpdated' => 'onSelectSearchUpdated',
        'select-search-updated' => 'applySelectValue'
    ];

     // Schemas por bloco
    public array $schemaProcesso = [];
    public array $schemaMercadorias = [];
    public array $schemaTransporte = [];
    public array $schemaFinanceiro = [];
    public array $schemaCrudExportacaoCampos = [];
    public array $schemaObservacoes = [];

    public array $descricoesSugeridasFixas = [
        'Congelados',
        'Plantas/Cereias/Sementes',
        'Maquinas/Auto',
        'Exportação de CRUD',
        'Madeira/Papel/Livros',
        'Minerais e Metais',
        'Vestuarios',
        'Electrónicos/Material Informático',
        'Material de Escritório'
    ];

    private $_empresa = null;

    /**
     * mount é chamado quando o componente é inicializado.
     *
     * Ele suporta:
     * - /processos/create  -> sem $processo (create)
     * - /processos/{processo}/edit -> com $processo (edit)
     */
    public function mount($mode = 'create', Processo $processo = null)
    {
        $this->_empresa = Auth::user()->empresas->first();

        $this->mode = $mode;

        // Se vier um processo (edit), usa-o; caso contrário, cria um novo
        $this->processo = $processo ?: new Processo();

        $this->processoId = $processo?->id;

        if ($this->mode === 'edit') {
            $this->form['customer_id']   = $this->processo->customer_id;
            $this->form['exportador_id'] = $this->processo->exportador_id;
        }

        $this->banks = IbanController::getBankDetails();

        $this->buildSchema();

        $this->hydrateFormFromModel();
    }

    // 2. Propriedade computada para sugestões dinâmicas
    public function getDescricoesSugeridasProperty(): array
    {
        $descricaoAtual = $this->form['Descricao'] ?? '';
        $descricaoAtual = trim($descricaoAtual);
        
        // Se tem menos de 3 caracteres, retorna apenas as sugestões fixas
        if (strlen($descricaoAtual) < 3) {
            return $this->descricoesSugeridasFixas;
        }
        
        // Buscar descrições similares do banco de dados
        $descricoesDoBanco = Processo::query()
            ->select('Descricao')
            ->distinct()
            ->whereNotNull('Descricao')
            ->where('Descricao', '!=', '')
            ->where('Descricao', 'LIKE', '%' . $descricaoAtual . '%')
            ->when($this->mode === 'edit' && $this->processo?->id, function ($query) {
                // Excluir a descrição do processo atual em modo edição
                $query->where('id', '!=', $this->processo->id);
            })
            ->limit(15) // Aumentei para 15 para ter mais opções
            ->orderByRaw('LENGTH(Descricao)') // Ordenar por menor comprimento primeiro
            ->pluck('Descricao')
            ->toArray();
        
        // Combinar e filtrar sugestões fixas que correspondam à busca
        $sugestoesFixasFiltradas = array_filter(
            $this->descricoesSugeridasFixas,
            function ($sugestao) use ($descricaoAtual) {
                return stripos($sugestao, $descricaoAtual) !== false;
            }
        );
        
        // Combinar todos os resultados, mantendo as fixas primeiro
        $todasSugestoes = array_unique(array_merge(
            $sugestoesFixasFiltradas,
            $descricoesDoBanco
        ));
        
        // Limitar total de sugestões
        return array_slice($todasSugestoes, 0, 20);
    }

    // 3. Método para buscar sugestões (usado pelo Alpine/Livewire)
    public function buscarSugestoes($query): array
    {
        if (strlen($query) < 2) {
            return $this->descricoesSugeridasFixas;
        }
        
        try {
            $descricoesDoBanco = Processo::query()
                ->whereNotNull('Descricao')
                ->where('Descricao', '!=', '')
                ->where('Descricao', 'like', '%' . $query . '%')
                ->select('Descricao')
                ->distinct()
                ->limit(10)
                ->pluck('Descricao')
                ->toArray();
            
            // Filtrar sugestões fixas
            $sugestoesFixasFiltradas = array_filter(
                $this->descricoesSugeridasFixas,
                function ($sugestao) use ($query) {
                    return stripos($sugestao, $query) !== false;
                }
            );
            
            return array_unique(array_merge($sugestoesFixasFiltradas, $descricoesDoBanco));
            
        } catch (\Exception $e) {
            // Em caso de erro, retorna apenas as fixas filtradas
            return array_filter(
                $this->descricoesSugeridasFixas,
                function ($sugestao) use ($query) {
                    return stripos($sugestao, $query) !== false;
                }
            );
        }
    }

    // 4. Método para atualizar datalist quando o campo muda
    public function updatedFormDescricao($value): void
    {
        // Disparar evento para atualizar o datalist se necessário
        if (strlen(trim($value)) >= 2) {
            $this->dispatch('descricao-alterada', descricao: $value);
        }
    }

     /**
     * Constrói o schema do formulário.
     */

    protected function buildSchema()
    {
        // resumo mínimo; estende conforme o teu model
        $this->schema = [
                'vinheta' => [
                'type' => 'text',
                'label' => 'Vinheta', 
                'placeholder' => 'Ex: V123456',
                'size' => 2, // Agora funciona!
            ],
            'RefCliente' => [
                'type' => 'text',
                'label' => 'Ref. Cliente', 
                'placeholder' => 'Ref. interna do cliente',
                'size' => 2, // 3 campos por linha
            ],
            'TipoProcesso' => [
                'type' => 'select',
                'label' => 'Tipo de Declaração',
                'options' => \App\Models\RegiaoAduaneira::pluck('descricao','id')->toArray(),
                'required' => true,
                'size' => 4, // 3 campos por linha
            ],
            'estancia_id' => [
                'type' => 'select',
                'label' => 'Estância',
                'options' => \App\Models\Estancia::pluck('desc_estancia','id')->toArray(),
                'required' => true,
                'size' => 4, // 3 campos por linha
            ],

            'customer_id' => [
                'type' => 'select-search',
                'label' => 'Cliente',
                'model' => \App\Models\CustomersEmpresa::class,
                'displayField' => 'customer.CompanyName',     // vem da relação
                'extraField'   => 'customer.CustomerTaxID',   // opcional
                'searchField'  => 'customer.CompanyName',
                'where' => [
                    ['empresa_id', '=', $this->_empresa->id]
                ],
                'field' => 'customer_id',   // ← este é o campo final que o form recebe
                'size' => 4, // 3 campos por linha
            ],

            'exportador_id' => [ 
                'type'          => 'select-search',
                'label'         => 'Exportador',
                'model'         => \App\Models\ExportadorEmpresa::class,
                'displayField' => 'exportador.Exportador',
                'extraField'   => 'exportador.ExportadorTaxID',
                'searchField'  => 'exportador.Exportador',
                'where'        => [
                    ['empresa_id', '=', $this->_empresa->id],
                ],
                'required'     => true,
                'field'        => 'exportador_id',
                'size' => 4, // 3 campos por linha
            ],

            'DataAbertura' => ['type'=>'date','label'=>'Abertura','default'=>date('Y-m-d'),'size' => 2],
            'DataChegada' => ['type'=>'date','label'=>'Data de Chegada','size' => 2],
            'Estado' => ['type'=>'select','label'=>'Estado','options'=>['Aberto'=>'Aberto','Em curso'=>'Em curso','Alfandega'=>'Alfandega','Finalizado'=>'Finalizado'],'size' => 4],

            // Dados do DU
            'NrDU' => ['type'=>'text','label'=>'Nº de Ordem / DU', 'size' => 2],
            'N_Dar' => ['type'=>'text','label'=>'Nº DAR', 'size' => 2],
            'MarcaFiscal' => ['type'=>'text','label'=>'Marca Fiscal', 'size' => 2],
            'BLC_Porte' => ['type'=>'text','label'=>'BLC / Porte', 'size' => 2],

            // 🔹 Mercadorias / Origem / Portos
            'Descricao' => [
                'type'=>'text',
                'label'=>'Descrição',
                'required'=>true, 
                'placeholder'=>'Descrição detalhada das mercadorias', 
                'size' => 6,
                'hint' => 'Digite 3 letras para ver sugestões. Pressione ↓ para ver todas.',
                'icon' => 'fas fa-list',
                'datalist' => 'descricoes-sugeridas',
            ],
            'peso_bruto' => ['type'=>'number','label'=>'Peso Bruto (Kg)','default'=>0, 'size' => 2],
            'localizacao_mercadoria_id' => ['type'=>'select','label'=>'Localização da Mercadoria','options'=>\App\Models\MercadoriaLocalizacao::query()->orderBy('descricao')->pluck('descricao', 'id')->toArray(), 'size'=>4],
            
            'Pais_origem' => ['type'=>'select','label'=>'País de Origem','options'=>\App\Models\Porto::query()->orderBy('pais')->pluck('pais', 'pais_id')->toArray(), 'size' => 4],
            'PortoOrigem' => ['type'=>'select','label'=>'Porto de Embarque','options'=>\App\Models\Porto::query()->orderBy('porto')->pluck('porto', 'sigla')->toArray(), 'size' => 4],
            'porto_desembarque_id' => ['type'=>'select','label'=>'Porto de Desembarque','options'=>\App\Models\Porto::query()->orderBy('porto')->pluck('porto', 'id')->toArray(), 'size' => 4],
            
            
            // 🔹 CRUD (Exportação de CRUD) – campos extras
            'data_carregamento' => ['type'=>'date','label'=>'Data de Carregamento','size'=>4],
            'quantidade_barris' => ['type'=>'number','label'=>'Quantidade de Barris','size'=>2,'default'=>0],
            'num_deslocacoes' => ['type'=>'number','label'=>'Número de Deslocações','size'=>2,'default'=>0],
            'valor_barril_usd' => ['type'=>'money','label'=>'Valor por Barril (USD)','currency'=>'USD','size'=>4,'default'=>0],
            'rsm_num' => ['type'=>'text','label'=>'Número RSM','size'=>4],
            'certificado_origem' => ['type'=>'text','label'=>'Certificado de Origem','size'=>4],
            'guia_exportacao' => ['type'=>'text','label'=>'Guia de Exportação','size'=>4],

            // transporte
            'registo_transporte' => ['type'=>'text','label'=>'Registo de Transporte','size'=>4],
            'TipoTransporte' => ['type'=>'select','label'=>'Tipo de Transporte','options'=>\App\Models\TipoTransporte::query()->orderBy('descricao')->pluck('descricao', 'id')->toArray(),'size'=>4],
            'nacionalidade_transporte' => ['type'=>'select','label'=>'Nacionalidade do Transporte', 'options' => \App\Models\Pais::query()->orderBy('pais')->pluck('pais', 'id')->toArray(),'size'=>4],
            
            // 🔹 Pagamentos / Banco
            'forma_pagamento' => ['type'=>'select','label'=>'Forma de Pagamento','options'=>[
                    'Tr' => 'Transferência Bancária',
                    'CK' => 'Caixa Única Tesouro Base Kwanda',
                    'RD' => 'Pronto Pagamento',
                    'Ou' => 'Outro',
                ],'size'=>4],

            'codigo_banco' => ['type'=>'select','label'=>'Código do Banco','options'=>$this->banks, 'required'=>false, 'size'=>4],
            'condicao_pagamento_id' => ['type'=>'select','label'=>'Condição de Pagamento','options'=>\App\Models\CondicaoPagamento::query()->orderBy('descricao')->pluck('descricao', 'id')->toArray(), 'size'=>4],
            // finance
            
            'Moeda' => ['type'=>'select','label'=>'Moeda', 'options'  => \App\Models\Pais::query()->where('cambio', '>', 0)->orderBy('moeda')->pluck('moeda', 'moeda')->toArray(),'default'=>'USD', 'size'=>2],
            'Cambio' => ['type'=>'number','label'=>'Cambio','size'=>2,'default'=>1],
            'fob_total' => ['type'=>'money','label'=>'FOB','currency'=>'USD', 'size'=>4],
            'frete' => ['type'=>'money','label'=>'Frete','currency'=>'USD', 'size'=>2],
            'seguro' => ['type'=>'money','label'=>'Seguro','currency'=>'USD', 'size'=>2],
            'cif' => ['type'=>'money','label'=>'CIF (USD)','currency'=>'USD','size'=>4,'disabled'=>true],
            'ValorAduaneiro' => ['type'=>'money','label'=>'Valor Aduaneiro (Kz)','currency'=>'Kz','size'=>4,'disabled'=>true],
            'observacoes' => ['type'=>'textarea','label'=>'Observações', 'placeholder'=>'Observações gerais sobre o processo', 'size'=>12],
            
        ];

        // podes estender aqui com mais campos (locais, porto, etc)
        $this->schemaCrudExportacaoCampos = collect($this->schema)->only([
            'data_carregamento',
            'quantidade_barris',
            'valor_barril_usd',
            'num_deslocacoes',
            'rsm_num',
            'certificado_origem',
            'guia_exportacao',
        ])->toArray();

        $this->schemaProcesso = collect($this->schema)->only([
            'vinheta',
            'RefCliente',
            'TipoProcesso',
            'estancia_id',
            'customer_id',
            'exportador_id',
            'DataAbertura',
            'DataChegada',
            'Estado',
            'NrDU',
            'N_Dar',
            'MarcaFiscal',
            'BLC_Porte',
        ])->toArray();

        $this->schemaMercadorias = collect($this->schema)->only([
            'Descricao',
            'peso_bruto',
            'Pais_origem',
            'PortoOrigem',
            'porto_desembarque_id',
            'localizacao_mercadoria_id',
        ])->toArray();

        $this->schemaTransporte = collect($this->schema)->only([
            'registo_transporte',
            'TipoTransporte',
            'nacionalidade_transporte',
        ])->toArray();

        $this->schemaFinanceiro = collect($this->schema)->only([
            'forma_pagamento',
            'codigo_banco',
            'condicao_pagamento_id',
            'fob_total',
            'frete',
            'seguro',
            'Moeda',
            'Cambio',
            'cif',
            'ValorAduaneiro',
        ])->toArray();

        $this->schemaObservacoes = collect($this->schema)->only([
            'observacoes',
        ])->toArray();
    }

    protected function hydrateFormFromModel()
    {
        foreach ($this->schema as $name => $conf) {
            $this->form[$name] = old($name, data_get($this->processo, $name, $conf['default'] ?? null));
        }
    }

    public function onSelectSearchUpdated($data)
    {
        foreach ($this->schema as $field => $conf) {
            if (($conf['type'] ?? null) === 'select-search' &&
                $data['value'] === $this->form[$field] ?? null) {
                $this->form[$field] = $data['id'];
            }
        }
    }

    #[On('select-search-updated')]
    public function setSelectSearch($field, $value)
    {
        $this->form[$field] = $value;
    }

    public function applySelectValue($field, $value)
    {
        $this->{$field} = $value;
    }

    /**
     * Regras de validação geradas a partir do schema.
     */
    protected function rules()
    {
        $rules = [];

        foreach ($this->schema as $name => $conf) {

            $r = [];

            // ✅ Obrigatório vs Nullable
            if (!empty($conf['required'])) {
                $r[] = 'required';
            } else {
                $r[] = 'nullable';
            }

            // ✅ Tipos
            switch ($conf['type'] ?? null) {

                case 'date':
                    $r[] = 'date';
                    break;

                case 'number':
                case 'money':
                    $r[] = 'numeric';
                    break;

                case 'email':
                    $r[] = 'email';
                    break;

                case 'select-search':
                    $r[] = 'integer'; // ✅ SEMPRE ID numérico
                    break;

                case 'select':

                    // 🔐 EXCEÇÃO PARA CAMPOS DE CÓDIGO (banco, moeda, etc.)
                    if (in_array($name, ['codigo_banco', 'Moeda'])) {
                        $r[] = 'string';
                        break;
                    }

                    // ✅ Se as options têm chaves numéricas → integer
                    if (!empty($conf['options']) && is_array($conf['options'])) {
                        $firstKey = array_key_first($conf['options']);

                        if (is_numeric($firstKey)) {
                            $r[] = 'integer';
                        } else {
                            $r[] = 'string';
                        }
                    }

                    break;
            }

            $rules["form.{$name}"] = implode('|', $r);
        }

        return $rules;
    }

    public function updated($field, $value)
    {
        // quaisquer alterações a campos específicos
        if (in_array($field, ['form.fob_total','form.frete','form.seguro','form.Cambio','form.ValorAduaneiro'])) {
            
            $this->recalcCifAndAduaneiro();
        }
    }

    protected function normalizeMoney($value)
    {
        if (!$value) return 0;

        return floatval(
            str_replace(',', '.', str_replace('.', '', $value))
        );
    }

    protected function recalcCifAndAduaneiro()
    {
        $fob    = floatval(str_replace(',', '.', str_replace('.', '', $this->form['fob_total'] ?? 0)));
        $frete  = floatval(str_replace(',', '.', str_replace('.', '', $this->form['frete'] ?? 0)));
        $seguro = floatval(str_replace(',', '.', str_replace('.', '', $this->form['seguro'] ?? 0)));
        $cambio = floatval(str_replace(',', '.', str_replace('.', '', $this->form['cambio'] ?? 1)));

        // ✅ CIF = FOB + FRETE + SEGURO
        $cif = $fob + $frete + $seguro;

        // ✅ VALOR ADUANEIRO = CIF * CAMBIO
        $valorAduaneiro = $cif * $cambio;

        $this->form['cif'] = number_format($cif, 2, ',', '.');
        $this->form['ValorAduaneiro'] = number_format($valorAduaneiro, 2, ',', '.');
    }


    /**
     * Guardar (create/update) o processo.
     */
    public function save()
    {
        // dd($this->form);

        try {
            foreach (['cif','ValorAduaneiro','fob_total','frete','seguro', 'Cambio'] as $field) {
                if (!empty($this->form[$field])) {
                    $this->form[$field] = floatval(
                        str_replace(['.',','], ['','.'], $this->form[$field])
                    );
                }
            }
            
            $this->validate();
            foreach ($this->schema as $name => $conf) {
                if (!empty($conf['readonly'])) continue;
                $this->processo->{$name} = $this->form[$name] ?? null;
            }

            $this->processo->cif = $this->form['cif'] ?? 0;
            $this->processo->ValorAduaneiro = $this->form['ValorAduaneiro'] ?? 0;

            $this->processo->empresa_id = Auth::user()->empresas->first()->id;
            $this->processo->user_id = Auth::user()->id;

            if ($this->mode === 'edit') {
                $this->processo->save();
                $msg = 'Processo actualizado com sucesso!';
            } else {
                $this->processo->save();
                $msg = 'Processo criado com sucesso!';
            }

            $this->dispatch('toast', type: 'success', message: $msg);

            return redirect()->route('processos.index');

        } catch (\Throwable $e) {

            logger()->error('ERRO LIVEWIRE SAVE', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    // quick modals callbacks
    public function onClienteCreated($payload)
    {
        // payload: ['id'=>..., 'label'=>...
        $this->form['customer_id'] = $payload['id'];
        $this->dispatch('toast', type: 'success', message: 'Cliente adicionado');

    }

    public function onExportadorCreated($payload)
    {
        $this->form['exportador_id'] = $payload['id'];
        $this->dispatch('toast', type: 'success', message: 'Exportador adicionado');

    }

    // Rascunhos
    public function saveDraft()
    {
        $draft = ProcessosDraft::create([
            'user_id' => Auth::id(),
            'empresa_id' => Auth::user()->empresas->first()->id ?? null,
            'NrProcesso' => $this->processo->NrProcesso ?? null,
            'payload' => json_encode($this->form),
        ]);

        $this->dispatchBrowserEvent('toast', ['type'=>'success','message'=>'Rascunho guardado']);
    }

    public function loadDraft($id)
    {
        $d = ProcessosDraft::find($id);
        if (!$d) return;
        $payload = json_decode($d->payload, true);
        foreach ($payload as $k => $v) $this->form[$k] = $v;
        $this->dispatchBrowserEvent('toast', ['type'=>'info','message'=>'Rascunho carregado']);
    }

    public function render()
    {
        return view('livewire.processos.form');
    }
}
