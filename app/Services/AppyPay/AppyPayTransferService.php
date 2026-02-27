<?php

namespace App\Services\AppyPay;

class AppyPayTransferService 
{
    // Serviço para gerir transferências 
    // Gerar dados bancários
    public function create(array $data): array 
    {
        // Exemplo de dados bancários para transferência
         return [
            'banco' => 'Banco BIC',
            'titular' => 'HONGAYETU LDA',
            'nib' => '0043.0000.12345678901.51',
            'iban' => 'AO06.0043.0000.12345678901.51',
            'swift' => 'BICBAOLUXXX',
            'valor' => $data['amount'],
            'referencia' => 'LOGIGATE-' . $data['subscription_id'],
            'descricao' => 'Pagamento plano ' . $data['plan_name']
        ];

        $this->response = [
            'method' => 'TRANSFER',
            'status' => 'pending',
            'transaction_id' => $this->transactionId,
            'bank_data' => $bankData,
            'instructions' => [
                '1. Utilize os dados bancários abaixo',
                '2. Efetue a transferência',
                '3. Faça upload do comprovativo',
                '4. Aguarde confirmação (1-2 dias úteis)'
            ]
        ];
    }
}