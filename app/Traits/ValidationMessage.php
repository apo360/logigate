<?php

// app/Traits/ValidationMessage.php
namespace App\Traits;

trait ValidationMessage
{

    public static function obterMensagensValidacao()
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'string' => 'O campo :attribute deve ser uma string.',
            'date' => 'O campo :attribute deve ser uma data válida.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            // Adicione outras mensagens conforme necessário
        ];
    }
}
