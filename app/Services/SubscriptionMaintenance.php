<?php

namespace App\Services;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Application\Billing\UseCases\HandleAppyPayWebhook;
use App\Models\PagamentoOnline;
use App\Models\Subscricao;
use App\Models\WebhookEvent;
use App\Notifications\SubscriptionRenewalNotice;
use App\Support\CompanyRbac;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class SubscriptionMaintenance
{
    public function expire(): int
    {
        // Expired dates already deny entitlement. Preserve active flags: false is an explicit revocation.
        return Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->active()
            ->whereNotNull('data_expiracao')->where('data_expiracao', '<=', now())
            ->update(['status' => Subscricao::STATUS_EXPIRADA]);
    }

    public function reconcile(): array
    {
        $counts = ['events_processed' => 0, 'activations_recovered' => 0, 'failed' => 0, 'waiting' => 0];
        WebhookEvent::where('provider', 'appypay')->whereIn('status', ['received', 'waiting_payment', 'failed'])
            ->whereNotNull('payload')->orderBy('id')->chunkById(100, function ($events) use (&$counts) {
                foreach ($events as $event) {
                    try {
                        $result = app(HandleAppyPayWebhook::class)->execute($event->payload, json_encode($event->payload), $event->event_id);
                        $counts[$result->result === 'payment_not_found' ? 'waiting' : ($result->result === 'invalid_payload' ? 'failed' : 'events_processed')]++;
                    } catch (\Throwable $error) {
                        $counts['failed']++;
                        Log::warning('BILLING_RECONCILIATION_FAILED', ['event_id' => $event->id]);
                    }
                }
            });
        PagamentoOnline::where('status', 'paid')->orderBy('id')->chunkById(100, function ($payments) use (&$counts) {
            foreach ($payments as $payment) {
                $sub = Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->whereKey($payment->subscription_id)->where('empresa_id', $payment->empresa_id)->first();
                if (! $sub || $sub->activated_at !== null || ! $sub->hasStatus('pendente')) {
                    continue;
                }
                try {
                    app(ActivateSubscriptionAction::class)->execute($sub);
                    $counts['activations_recovered']++;
                } catch (\Throwable $error) {
                    $counts['failed']++;
                    Log::warning('BILLING_ACTIVATION_RECOVERY_FAILED', ['payment_id' => $payment->id]);
                }
            }
        });

        return $counts;
    }

    public function renewalNotices(): int
    {
        $count = 0;
        Subscricao::withoutGlobalScopes()->whereNull('deleted_at')->effective()->whereNull('renewal_notified_at')
            ->where('data_expiracao', '<=', now()->addDays(config('billing.renewal_notice_days')))->orderBy('id')
            ->chunkById(100, function ($subs) use (&$count) {
                foreach ($subs as $sub) {
                    DB::transaction(function () use ($sub, &$count) {
                        $locked = Subscricao::withoutGlobalScopes()->whereKey($sub->id)->lockForUpdate()->firstOrFail();
                        if ($locked->renewal_notified_at !== null) {
                            return;
                        }
                        $users = $locked->empresa->users()->get()->filter(fn ($user) => CompanyRbac::within($locked->empresa_id, fn () => $user->hasAnyRole(config('billing.management_roles'))));
                        foreach ($users as $user) {
                            $user->notify(new SubscriptionRenewalNotice($locked));
                        }
                        $locked->update(['renewal_notified_at' => now()]);
                        $count++;
                    });
                }
            });

        return $count;
    }
}
