<?php

namespace App\Domains\Licenciamento\Data;

final class LicenciamentoFormData {

    public function __construct(
        public readonly array $attributes,
    ){
        
    }

    public static function fromArray(array $form): self
    {
        $attributes = $form;

        foreach ([
            'estancia_id', 'cliente_id', 'exportador_id',
            'tipo_declaracao', 'tipo_transporte', 'codigo_banco',
            'qntd_volume', 'pais_origem', 'cif',
        ] as $field) {        
            $attributes[$field] = isset($form[$field]) && $form[$field] !== '' ? (int) $form[$field] : null;
        }
        
        return new self(attributes:$attributes);
    }
}