<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Produto extends Model
{
    use HasFactory;

    // Defina uma propriedade para identificar qual tabela os atributos pertencem
    protected $tableIdentifier = ''; // Por padrão, nenhum identificador

    /**
     * Construtor da classe Produto.
     * Define os atributos que podem ser preenchidos em massa
     * 
     * @param array $attributes Atributos do modelo
     */

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Obter a lista de colunas da tabela produtos usando o Schema do Laravel
        $this->fillable = Schema::getColumnListing($this->table);

        // Obter a lista da tabela produtos_prices
        $this->setTableIdentifier();
    }

    protected function setTableIdentifier()
    {
        // Verifique se algum atributo pertence à tabela 'product_prices'
        $priceAttributes = Schema::getColumnListing('product_prices');

        foreach ($priceAttributes as $attribute) {
            if (!empty($this->$attribute)) {
                $this->tableIdentifier = 'product_prices';
                break; // Se um atributo for definido, pare de verificar
            }
        }
    }

    public function getTable()
    {
        return $this->tableIdentifier ?: parent::getTable();
    }
}
