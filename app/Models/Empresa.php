<?php

namespace App\Models;

use App\Domains\Empresa\Services\GeradorCodigoContaEmpresaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\ActivatedModule;
use App\Models\Subscricao;
use App\Models\EmpresaUser;

class Empresa extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $table = "empresas";

    protected $fillable = [
        'CodFactura',
        'CodProcesso',
        'Empresa',
        'ActividadeComercial',
        'Designacao',
        'NIF',
        'Cedula',
        'Logotipo',
        'Slogan',
        'Endereco_completo',
        'Provincia',
        'Cidade',
        'Dominio',
        'Email',
        'Fax',
        'Contacto_movel',
        'Contacto_fixo',
        'Sigla',
        'ativo',
        'conta',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('ativo', 1);
    }

    public static function lastId(): int
    {
        return (int) self::max('id') + 1;
    }

     /**
     * Relação N:N com clientes via tabela pivô customers_empresas
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customers_empresas')
                    ->withPivot(['codigo_cliente', 'additional_info'])->withTimestamps();
    }
    /**
     * Relação N:N com Exportadors via tabela pivô exportador_empresas
     */
    public function exportadors()
    {
        return $this->belongsToMany(Exportador::class, 'exportador_empresas')
                    ->withPivot(['codigo_exportador', 'additional_info', 'status', 'data_associacao'])->withTimestamps();
    }
    /**
     * Relação 1:N — Uma empresa pode ter vários representantes
     */
    public function representantes()
    {
        return $this->hasMany(Representante::class, 'empresa_id');
    }

    // Definir relacionamento com usuários
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_users');
    }

    public function activatedModules()
    {
        return $this->hasMany(ActivatedModule::class);
    }

    public function subscricoes(): HasMany
    {
        return $this->hasMany(Subscricao::class, 'empresa_id', 'id');
    }

    public function integracoes(): HasMany
    {
        return $this->hasMany(EmpresaIntegracao::class, 'empresa_id', 'id');
    }

    public function documentosArquivos(): HasMany
    {
        return $this->hasMany(DocumentoArquivo::class, 'empresa_id', 'id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'Provincia', 'id');
    }

    // Function Boot
    public static function boot()
    {
        parent::boot();

        // Criar um evento de "created" para a empresa
        static::creating(function ($empresa) {
            // TODO: remover este fallback quando todos os fluxos criarem empresa via CriarEmpresaAction.
            if (! $empresa->conta) {
                $empresa->conta = app(GeradorCodigoContaEmpresaService::class)->gerar();
            }
        });

        // Criar um evento de "deleting" para a empresa
        static::deleting(function ($empresa) {
            // Excluir os usuários associados à empresa
            $empresa->users()->detach();

            // Excluir os módulos ativados para a empresa
            $empresa->activatedModules()->delete();

            // Excluir as subscrições associadas à empresa
            $empresa->subscricoes()->delete();
        });
    } 

    /**
     * Obter a subscrição ativa
     */
    public function subscricaoAtiva()
    {
        return $this->hasOne(Subscricao::class, 'empresa_id')
            ->whereIn('status', Subscricao::activeStatuses())
            ->where(function ($query) {
                $query->whereNull('data_expiracao')
                    ->orWhere('data_expiracao', '>', now());
            })
            ->latest('id');
    }

    /**
     * Relação 1:N — Uma empresa pode ter vários processos
     */
    public function processos()
    {
        return $this->hasMany(Processo::class, 'empresa_id');
    }

    /**
     * Relação 1:N - Uma empresa pode ter vários processos rascunhos
     */
    public function processosRascunhos()
    {
        return $this->hasMany(ProcessosDraft::class, 'empresa_id')->where('status', 'RASCUNHO');
    }

    /**
     * Relação 1:N — Uma empresa pode ter vários licenciaments
     */
    public function licenciaments()
    {
        return $this->hasMany(Licenciamento::class, 'empresa_id');
    }

    /**
     * Relação 1:N — Uma empresa pode ter vários documentos de Vendas
     */
    public function Facturas()
    {
        return $this->hasMany(SalesInvoice::class, 'empresa_id');
    }
}
