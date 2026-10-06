<?php

namespace App\Console\Commands;

use App\Application\Marketplace\MarketplaceExperience;
use App\Models\MarketplaceProfile;
use Illuminate\Console\Command;

class MarketplaceRefreshCommand extends Command
{
    protected $signature = 'marketplace:refresh {profile : ID público autorizado}';
    protected $description = 'Actualizar resumo agregado de uma empresa autorizada, sem modificar operações.';

    public function handle(MarketplaceExperience $experience): int
    {
        try {
            $experience->refresh(MarketplaceProfile::findOrFail($this->argument('profile')));
        } catch (\LogicException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
        $this->info('Resumo actualizado. Não foram alterados processos ou licenciamentos.');
        return self::SUCCESS;
    }
}
