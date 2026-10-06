<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\MarketplaceProfile;
use App\Models\PautaAduaneira;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarketplaceProfileCommand extends Command
{
    protected $signature = 'marketplace:profile {empresa} {--name=} {--location=} {--provider} {--consent=} {--history-reviewed} {--specialties=} {--publish} {--withdraw}';
    protected $description = 'Registar adesão pública explícita; novos perfis permanecem ocultos por defeito.';

    public function handle(): int
    {
        $empresa = Empresa::findOrFail($this->argument('empresa'));
        $profile = MarketplaceProfile::firstOrNew(['empresa_id' => $empresa->id]);
        if ($this->option('withdraw')) {
            if ($profile->exists) $profile->update(['published' => false, 'history_authorized' => false, 'calculated_at' => null]);
            $this->info('Publicação retirada.');
            return self::SUCCESS;
        }
        if (!$this->option('name') || !$this->option('provider') || !$this->option('consent')
            || !in_array($empresa->Designacao, ['Despachante Oficial', 'Praticante'], true) || !$empresa->ativo) {
            $this->error('Indique nome público, prestação de serviços e referência do consentimento; a empresa deve ser elegível e activa.');
            return self::FAILURE;
        }
        $ids = array_filter(explode(',', (string) $this->option('specialties')));
        $specialties = [];
        foreach ($ids as $id) {
            if (!ctype_digit($id)) { $this->error('Especialidades devem ser IDs pautais.'); return self::FAILURE; }
            $specialties[] = PautaAduaneira::findOrFail((int) $id);
        }
        DB::transaction(function () use ($profile, $specialties) {
            $profile->fill([
                'public_name' => $this->option('name'), 'public_location' => $this->option('location'),
                'service_provider' => true, 'consent_reference' => $this->option('consent'),
                'published' => (bool) $this->option('publish'),
                'history_authorized' => (bool) $this->option('history-reviewed'),
                'history_reviewed_at' => $this->option('history-reviewed') ? now() : null,
                'calculated_at' => null,
            ])->save();
            DB::table('marketplace_specialties')->where('profile_id', $profile->id)->delete();
            foreach (collect($specialties)->unique('id') as $item) {
                DB::table('marketplace_specialties')->insert(['profile_id' => $profile->id, 'pauta_id' => $item->id, 'codigo' => (string) $item->codigo]);
            }
        });
        $this->info($profile->published ? 'Perfil publicado por adesão explícita.' : 'Perfil guardado e oculto.');
        return self::SUCCESS;
    }
}
