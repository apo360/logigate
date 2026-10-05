<?php
namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

final class OperationalAuditObserver
{
    public function created(Model $model): void { $this->record($model, 'created', [], $model->getAttributes()); }
    public function updated(Model $model): void
    {
        $new = $model->getChanges();
        $old = array_intersect_key($model->getRawOriginal(), $new);
        $this->record($model, 'updated', $old, $new);
    }
    public function deleted(Model $model): void { $this->record($model, 'deleted', $model->getRawOriginal(), []); }
    private function record(Model $model, string $event, array $old, array $new): void
    {
        // Processo's Auditable integration owns HTTP events; console is disabled in that package's config.
        if ($model instanceof \App\Models\Processo && ! app()->runningInConsole()) { return; }
        $actor = \App\Support\ActorContext::user();
        $empresaId = \App\Support\TenantContext::empresaId($actor);
        if (! $actor || ! $empresaId) { return; }
        if (! Schema::hasTable('audits')) {
            Log::warning('Auditoria operacional indisponível: tabela audits ausente.', ['entity' => get_class($model), 'id' => $model->getKey()]);
            return;
        }
        $correlation = request()->attributes->get('v1_operation_id');
        if (! $correlation) {
            $correlation = (string) Str::uuid();
            request()->attributes->set('v1_operation_id', $correlation);
        }
        Audit::create([
            'user_type' => get_class($actor), 'user_id' => $actor->id,
            'event' => $event, 'auditable_type' => get_class($model), 'auditable_id' => $model->getKey(),
            'old_values' => array_intersect_key($old, array_flip($model->getFillable())),
            'new_values' => array_intersect_key($new, array_flip($model->getFillable())),
            'tags' => 'empresa:' . $empresaId . ',operation:' . $correlation,
            'url' => \App\Support\ActorContext::requestUrl(),
            'ip_address' => \App\Support\ActorContext::ipAddress(),
            'user_agent' => \App\Support\ActorContext::userAgent(),
        ]);
    }
}
