<?php

namespace App\Livewire;

use App\Models\PagamentoOnline;
use Livewire\Component;
use App\Models\Plano;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutPayment extends Component
{
    public Plano $plano;

    public string $cycle = 'monthly';
    public string $method = 'GPO';
    public ?string $phone = null;
    public float $amount = 0;
    public ?int $empresa_id = null;

    public bool $loading = false;
    public bool $showPhoneField = false;
    public bool $showCardFields = false;
    public bool $showRefInfo = false;
    public bool $showTransferInfo = false;
    public bool $processing = false;
    public ?string $error = null;
    public ?array $response = null;
    public ?string $paymentStatus = null;
    public ?array $referenceData = null;
    public ?string $transactionId = null;

    protected $rules = [
        'method' => 'required|in:GPO,REF,TRANSFER',
        'phone' => ['required_if:method,GPO', 'nullable', 'regex:/^9[0-9]{8}$/'],
    ];
    
    protected $messages = [
        'phone.required_if' => 'O número de telefone é obrigatório para pagamento GPO',
        'phone.regex' => 'Formato inválido. Use: 9XXXXXXXX (9 dígitos)',
    ];

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user) {
            redirect()->route('login');
            return;
        }

        $empresa = $user->empresas->first();

        if (!$empresa) {
            redirect()->route('dashboard')->with('error', 'Nenhuma empresa encontrada.');
            return;
        }

        $this->empresa_id = $empresa->id;

        // 🔎 Buscar subscrição pendente
        $subscription = $empresa->subscricoes()
            ->where('status', 'pendente')
            ->latest()
            ->first();

        if (!$subscription) {
            redirect()->route('plans')->with('error', 'Nenhuma subscrição pendente encontrada.');
            return;
        }

        $this->plano = Plano::findOrFail($subscription->plano_id);
        $this->cycle = $subscription->modalidade_pagamento;

        $this->calculateAmount();

        // 🔎 Buscar pagamento existente
        $payment = $subscription->pagamentoOnline()
            ->whereIn('status', ['pending', 'processing', 'waiting', 'paid'])
            ->latest()
            ->first();

        if (!$payment) {
            $this->updateMethodFields();
            return;
        }

        // ⏱ Verificar expiração
        $expired = $this->checkPaymentExpiration($payment);

        if ($expired) {
            $payment->update(['status' => 'expired']);
            $this->updateMethodFields();
            return;
        }

        // 🎯 Carregar estado na UI
        $this->loadExistingPayment($payment);
    }

    /**
     * Verifica se o pagamento expirou baseado no método
     */
    private function checkPaymentExpiration($payment): bool
    {
        $now = Carbon::now();
        
        return match($payment->method) {
            'GPO' => $payment->created_at->addMinutes(3)->isPast(),
            'REF' => $payment->created_at->addDays(15)->isPast(),
            'TRANSFER' => $payment->created_at->addDays(2)->isPast(),
            default => false,
        };
    }

    /**
     * Carrega dados de pagamento existente
     */
    private function loadExistingPayment($payment): void
    {
        $this->method = $payment->method;
        $this->paymentStatus = $payment->status;
        $this->transactionId = $payment->merchant_transaction_id;
        $this->phone = $payment->phone ?? null;

        // Normalizar resposta
        if ($payment->raw_response) {
            $rawResponse = $payment->raw_response;
            
            if (isset($rawResponse['response'])) {
                $this->response = $this->normalizeResponse([
                    'response' => $rawResponse['response']
                ]);
            } elseif (is_array($rawResponse)) {
                $this->response = $this->normalizeResponse([
                    'response' => $rawResponse
                ]);
            }
        }

        $this->updateMethodFields();
    }

    /**
     * Atualiza campos baseado no método selecionado
     */
    private function updateMethodFields(): void
    {
        $this->showPhoneField = ($this->method === 'GPO');
        $this->showCardFields = ($this->method === 'CARD');
        $this->showRefInfo = ($this->method === 'REF');
        $this->showTransferInfo = ($this->method === 'TRANSFER');
    }

    public function calculateAmount(): void
    {
        $this->amount = match($this->cycle) {
            'monthly' => $this->plano->preco_mensal,
            'trimestral' => $this->plano->preco_trimestral,
            'semestral' => $this->plano->preco_semestral,
            'anual' => $this->plano->preco_annual,
            default => $this->plano->preco_mensal,
        };
        
        // Adicionar IVA 14%
        $this->amount = round($this->amount * 1.14);
    }

    public function updatedMethod($value): void
    {
        $this->method = $value;
        $this->updateMethodFields();
        $this->resetError();
        
        // Limpar resposta anterior ao mudar método
        if ($this->response) {
            $this->response = null;
            $this->paymentStatus = null;
            $this->transactionId = null;
        }
    }

    public function updatedPhone(): void
    {
        $this->validateOnly('phone');
    }

    public function checkPaymentStatus(): void
    {
        if (!$this->transactionId) {
            return;
        }

        try {
            $payment = PagamentoOnline::where('merchant_transaction_id', $this->transactionId)
                ->latest()
                ->first();

            if (!$payment) {
                return;
            }

            // Verificar expiração
            if ($payment->status !== 'paid' && $this->checkPaymentExpiration($payment)) {
                $payment->update(['status' => 'expired']);
            }

            // Atualizar UI apenas se o status mudou
            if ($this->paymentStatus !== $payment->status) {
                $this->paymentStatus = $payment->status;
                
                if ($payment->status === 'paid') {
                    $this->dispatch('payment-success', ['plan' => $this->plano->nome]);
                } elseif ($payment->status === 'expired') {
                    $this->dispatch('payment-expired');
                }
            }

            // Atualizar resposta se disponível
            if ($payment->raw_response && $payment->status !== $this->response['status'] ?? null) {
                $rawResponse = $payment->raw_response;
                
                if (isset($rawResponse['response'])) {
                    $this->response = $this->normalizeResponse([
                        'response' => $rawResponse['response']
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do pagamento', [
                'transaction_id' => $this->transactionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function submit(CheckoutService $checkout): void
    {
        $this->validate();

        $this->processing = true;
        $this->error = null;

        // 📱 Normalizar telefone GPO
        if ($this->method === 'GPO' && $this->phone) {
            $this->phone = $this->normalizePhone($this->phone);
        }

        $lock = Cache::lock('checkout_' . Auth::id(), 10);

        if (!$lock->get()) {
            $this->error = 'Processamento já em andamento.';
            $this->processing = false;
            return;
        }

        try {
            $empresa = Auth::user()->empresas->first();

            if (!$empresa) {
                throw new \RuntimeException('Empresa não encontrada.');
            }

            $result = $checkout->pay([
                'plano' => $this->plano,
                'cycle' => $this->cycle,
                'method' => $this->method,
                'phone' => $this->phone,
                'empresa' => $empresa,
                'amount' => $this->amount,
            ]);

            if (!is_array($result)) {
                throw new \RuntimeException('Resposta inválida do serviço de pagamento.');
            }

            // 🔁 Caso pagamento já exista (idempotência)
            if (isset($result['message']) && $result['message'] === 'Pagamento já iniciado.') {
                $this->handleExistingPayment($result);
                return;
            }

            // 🎯 Processar nova resposta
            $this->processNewPayment($result);

        } catch (\Throwable $e) {
            $this->handlePaymentError($e);
        } finally {
            optional($lock)->release();
            $this->processing = false;
        }
    }

    /**
     * Normaliza número de telefone para formato internacional
     */
    private function normalizePhone(string $phone): string
    {
        // Remover tudo que não é número
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remover 244 se já estiver presente
        $phone = preg_replace('/^244/', '', $phone);
        
        // Garantir que tem 9 dígitos
        if (strlen($phone) === 9) {
            return '244' . $phone;
        }
        
        return $phone;
    }

    /**
     * Processa pagamento existente
     */
    private function handleExistingPayment(array $result): void
    {
        $responseData = $result['response'] ?? [];
        
        $this->response = $this->normalizeResponse([
            'response' => $responseData
        ]);
        
        $this->paymentStatus = $this->response['status'] ?? null;
        $this->transactionId = $responseData['id'] ?? $responseData['merchantTransactionId'] ?? null;
        
        $this->dispatch('payment-already-started');
    }

    /**
     * Processa novo pagamento
     */
    private function processNewPayment(array $result): void
    {
        $this->response = $this->normalizeResponse($result);
        $this->paymentStatus = $this->response['status'] ?? null;
        
        // Extrair transaction ID
        $gatewayResponse = $result['response'] ?? [];
        $this->transactionId = $gatewayResponse['id'] 
            ?? $gatewayResponse['merchantTransactionId'] 
            ?? $gatewayResponse['transactionId'] 
            ?? null;

        // Disparar evento baseado no status
        if ($this->paymentStatus === 'pending') {
            $this->dispatch('payment-initiated', [
                'method' => $this->method,
                'transaction' => $this->transactionId
            ]);
        }
    }

    /**
     * Trata erros de pagamento
     */
    private function handlePaymentError(\Throwable $e): void
    {
        Log::error('Erro ao processar pagamento', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'empresa_id' => $this->empresa_id,
            'method' => $this->method
        ]);

        $this->error = match(true) {
            str_contains($e->getMessage(), 'timeout') => 'Tempo limite excedido. Tente novamente.',
            str_contains($e->getMessage(), 'connection') => 'Erro de conexão. Tente novamente.',
            default => 'Erro ao processar pagamento: ' . $e->getMessage(),
        };
    }

    /**
     * Normaliza resposta do gateway para formato da UI
     */
    private function normalizeResponse(array $result): array
    {
        $gateway = $result['response'] ?? [];
        
        // Extrair status da resposta
        $responseStatus = $gateway['response']['responseStatus'] ?? $gateway['responseStatus'] ?? [];
        $status = $responseStatus['status'] ?? null;
        $message = $responseStatus['message'] ?? 'Pedido recebido';

        $normalized = [
            'method' => $this->method,
            'status' => $this->mapPaymentStatus($status),
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        // Adicionar dados específicos por método
        if ($this->method === 'REF') {
            $normalized = array_merge($normalized, $this->normalizeReferenceData($responseStatus));
        } elseif ($this->method === 'TRANSFER') {
            $normalized = array_merge($normalized, $this->getTransferData());
        }

        return $normalized;
    }

    /**
     * Mapeia status do gateway para status interno
     */
    private function mapPaymentStatus(?string $status): string
    {
        return match($status) {
            'Success', 'PAID', 'paid', 'success' => 'paid',
            'Pending', 'pending', 'waiting', 'processing' => 'pending',
            'Failed', 'failed', 'error' => 'failed',
            'Expired', 'expired' => 'expired',
            default => 'pending',
        };
    }

    /**
     * Normaliza dados de referência
     */
    private function normalizeReferenceData(array $responseStatus): array
    {
        $reference = $responseStatus['reference'] ?? [];
        
        return [
            'reference' => [
                'entity' => $reference['entity'] ?? '12345',
                'reference_number' => $reference['referenceNumber'] ?? $reference['reference_number'] ?? null,
                'due_date' => $reference['dueDate'] ?? $reference['due_date'] ?? now()->addDays(15)->format('Y-m-d'),
            ]
        ];
    }

    /**
     * Retorna dados de transferência bancária
     */
    private function getTransferData(): array
    {
        $reference = 'LOGIGATE-' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        return [
            'bank_data' => [
                'banco' => 'Banco BIC',
                'titular' => 'HONGAYETU LDA',
                'nib' => '0043.0000.12345678901.51',
                'iban' => 'AO06.0043.0000.12345678901.51',
                'swift' => 'BICBAOLUXXX',
                'valor' => $this->amount,
                'moeda' => 'AOA',
                'referencia' => $reference,
                'descricao' => 'Pagamento plano ' . $this->plano->nome . ' - ' . ucfirst($this->cycle),
                'instructions' => [
                    'Utilize os dados bancários abaixo para transferência',
                    'Inclua a referência no descritivo do pagamento',
                    'Após transferência, faça upload do comprovativo',
                    'Aguarde confirmação (até 2 dias úteis)'
                ]
            ]
        ];
    }

    /**
     * Atualiza assinatura (chamado após confirmação)
     */
    public function updateSubscription(CheckoutService $checkout): void
    {
        try {
            $empresa = Auth::user()->empresas->first();
            
            if (!$empresa) {
                throw new \RuntimeException('Empresa não encontrada.');
            }

            $this->calculateAmount();
            
            $checkout->updateSubscription([
                'plano' => $this->plano,
                'cycle' => $this->cycle,
                'empresa' => $empresa,
            ], $this->amount);

            $this->dispatch('subscription-updated');
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar assinatura', [
                'error' => $e->getMessage()
            ]);
            
            $this->error = 'Erro ao atualizar assinatura. Contate o suporte.';
        }
    }

    /**
     * Reseta erro
     */
    public function resetError(): void
    {
        $this->error = null;
    }

    /**
     * Cancela pagamento atual
     */
    public function cancelPayment(): void
    {
        $this->response = null;
        $this->paymentStatus = null;
        $this->transactionId = null;
        $this->error = null;
        
        $this->dispatch('payment-cancelled');
    }

    /**
     * Renderiza o componente
     */
    public function render()
    {
        return view('livewire.checkout-payment');
    }
}