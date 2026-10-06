<?php

namespace Tests\Support;

use App\Models\Plano;
use App\Models\PlanoItem;
use Illuminate\Support\Collection;

final class LandingFixtures
{
    public static function plans(): Collection
    {
        return collect([
            ['id' => 901, 'nome' => 'Plano de teste A', 'descricao' => 'Condições fictícias, apenas para validação local.', 'preco_mensal' => 0, 'preco_semestral' => 0, 'preco_anual' => 0],
            ['id' => 902, 'nome' => 'Plano de teste B', 'descricao' => 'Condições fictícias, apenas para validação local.', 'preco_mensal' => 12500.50, 'preco_semestral' => 75003, 'preco_anual' => 150006],
            ['id' => 903, 'nome' => 'Plano de teste C', 'descricao' => 'Condições fictícias, apenas para validação local.', 'preco_mensal' => 30000, 'preco_semestral' => null, 'preco_anual' => 360000],
        ])->map(function (array $attributes): Plano {
            $plan = new Plano($attributes);
            $plan->id = $attributes['id'];
            $item = new PlanoItem;
            $item->item = 'Funcionalidade de teste';
            $plan->setRelation('itemplano', collect([$item]));
            return $plan;
        });
    }
}
