<?php

namespace App\Livewire;

use App\Application\Billing\DTOs\StartCheckoutData;
use App\Application\Billing\UseCases\BuildPaymentViewData;
use App\Application\Billing\UseCases\StartCheckout;
use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CheckoutPayment extends Component
{
    public string $method = 'GPO';
    public ?string $phone = null;
    public ?int $empresaId = null;
    public ?int $subscriptionId = null;
    public ?int $paymentId = null;
    public ?string $paymentStatus = null;
    public ?string $merchantTransactionId = null;
    public ?string $error = null;
    public bool $processing = false;
    public array $paymentView = [];

    protected function rules(): array
    {
        return [
            'method' => ['required', 'in:GPO,REF'],
            'phone' => ['required_if:method,GPO', 'nullable', 'regex:/^9[0-9]{8}$/'],
        ];
    }

    protected array $messages = [
        'phone.required_if' => 'O numero de telefone e obrigatorio para pagamento GPO.',
        'phone.regex' => 'Formato invalido. Use: 9XXXXXXXX.',
    ];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            redirect()->route('login');
            return;
        }

        $empresa = $user->empresas()->first();

        if (! $empresa) {
            redirect()->route('home')->with('error', 'Nenhuma empresa encontrada.');
            return;
        }

        $this->empresaId = $empresa->id;
        $this->loadCheckoutState();
    }

    public function updatedMethod(string $value): void
    {
        $this->method = $value;
        $this->error = null;
        $this->resetValidation();
    }

    public function updatedPhone(): void
    {
        $this->validateOnly('phone');
    }

    public function submit(StartCheckout $startCheckout): void
    {
        $this->validate();
        $this->startPayment($startCheckout, PaymentMethod::fromInput($this->method));
    }

    public function retryGpo(StartCheckout $startCheckout): void
    {
        $this->method = PaymentMethod::GPO->value;
        $this->startPayment($startCheckout, PaymentMethod::GPO);
    }

    public function switchToRef(StartCheckout $startCheckout): void
    {
        $this->method = PaymentMethod::REF->value;
        $this->phone = null;
        $this->resetValidation();
        $this->startPayment($startCheckout, PaymentMethod::REF);
    }

    public function refreshPaymentStatus(): void
    {
        if (! $this->paymentId) {
            return;
        }

        $payment = PagamentoOnline::with('subscription.plano')->find($this->paymentId);

        if (! $payment || ! $payment->subscription) {
            return;
        }

        $this->applyPaymentView(app(BuildPaymentViewData::class)->fromSubscription($payment->subscription, $payment));
    }

    private function startPayment(StartCheckout $startCheckout, PaymentMethod $method): void
    {
        if (! $this->empresaId) {
            $this->error = 'Empresa nao encontrada.';
            return;
        }

        $lock = Cache::lock('checkout_' . Auth::id(), 10);

        if (! $lock->get()) {
            $this->error = 'Processamento ja em andamento.';
            return;
        }

        $this->processing = true;
        $this->error = null;

        try {
            $viewData = $startCheckout->execute(new StartCheckoutData(
                (int) Auth::id(),
                $this->empresaId,
                $method,
                $this->phone,
            ));

            $this->applyPaymentView($viewData);
        } catch (\Throwable $exception) {
            Log::error('BILLING_CHECKOUT_ERROR', [
                'user_id' => Auth::id(),
                'empresa_id' => $this->empresaId,
                'method' => $method->value,
                'error' => $exception->getMessage(),
            ]);

            $this->error = $exception instanceof \InvalidArgumentException
                ? $exception->getMessage()
                : 'Erro ao processar pagamento. Tente novamente.';
        } finally {
            optional($lock)->release();
            $this->processing = false;
        }
    }

    private function loadCheckoutState(): void
    {
        $subscription = Subscricao::with(['plano', 'pagamentoOnline'])
            ->where('empresa_id', $this->empresaId)
            ->pending()
            ->latest('id')
            ->first();

        if (! $subscription) {
            redirect()->route('home')->with('error', 'Nenhuma subscricao pendente encontrada.');
            return;
        }

        $this->subscriptionId = $subscription->id;

        $payment = $subscription->pagamentoOnline()
            ->whereIn('method', [PaymentMethod::GPO->value, PaymentMethod::REF->value])
            ->whereIn('status', [
                PaymentStatus::Pending->value,
                PaymentStatus::Processing->value,
                PaymentStatus::Paid->value,
                PaymentStatus::Failed->value,
                PaymentStatus::Expired->value,
            ])
            ->latest('id')
            ->first();

        $this->applyPaymentView(app(BuildPaymentViewData::class)->fromSubscription($subscription, $payment));
    }

    private function applyPaymentView(object $viewData): void
    {
        $this->paymentView = $viewData->toArray();
        $this->method = $this->paymentView['method'];
        $this->paymentId = $this->paymentView['payment_id'];
        $this->paymentStatus = $this->paymentView['status'];
        $this->merchantTransactionId = $this->paymentView['merchant_transaction_id'];

        if (($this->paymentView['phone'] ?? null) && str_starts_with($this->paymentView['phone'], '244')) {
            $this->phone = substr($this->paymentView['phone'], 3);
        }
    }

    public function getShouldPollProperty(): bool
    {
        return ($this->paymentView['method'] ?? null) === PaymentMethod::GPO->value
            && in_array($this->paymentStatus, [PaymentStatus::Pending->value, PaymentStatus::Processing->value], true);
    }

    public function getHasPaymentProperty(): bool
    {
        return $this->paymentId !== null;
    }

    public function getBaseAmountProperty(): float
    {
        return round(((float) ($this->paymentView['amount'] ?? 0)) / 1.14, 2);
    }

    public function getVatAmountProperty(): float
    {
        return round(((float) ($this->paymentView['amount'] ?? 0)) - $this->baseAmount, 2);
    }

    public function render()
    {
        return view('livewire.checkout-payment', [
            'statusEnum' => $this->paymentStatus ? PaymentStatus::fromPersisted($this->paymentStatus) : PaymentStatus::Pending,
            'cycleEnum' => isset($this->paymentView['cycle']) ? BillingCycle::fromInput($this->paymentView['cycle']) : BillingCycle::Monthly,
        ]);
    }
}
