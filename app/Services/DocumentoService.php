<?php

namespace App\Services;

use App\Models\SalesDocTotal;
use App\Models\SalesInvoice;
use Exception;

class DocumentoService
{
    public function signAndSaveHash(int $invoiceId): void
    {
        $invoice = SalesInvoice::findOrFail($invoiceId);
        $docTotal = SalesDocTotal::where('documentoID', $invoiceId)->firstOrFail();

        $messageToSign = implode(';', [
            $invoice->invoice_date,
            $invoice->getSystemEntryDate(),
            $invoice->invoice_no,
            $docTotal->gross_total,
        ]);

        file_put_contents(storage_path('app/temp_message.txt'), $messageToSign);

        $privateKeyPath = '/www/wwwroot/aduaneiro.hongayetu.com/ocean_system/sea/weave/fechadura_rest.pem';

        if (! file_exists($privateKeyPath)) {
            throw new Exception('Private key file not found.');
        }

        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath), null);
        if (! $privateKey) {
            throw new Exception('Failed to load private key.');
        }

        if (! openssl_sign($messageToSign, $signature, $privateKey, OPENSSL_ALGO_SHA1)) {
            throw new Exception('Failed to sign the message.');
        }

        $invoice->hash = base64_encode($signature);

        if (! $invoice->save()) {
            throw new Exception('Failed to save invoice hash.');
        }
    }
}
