<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

function processoCol($p)
{
    return '
        <div>
            <a href="'.route('customers.show',$p->cliente->id).'" 
               class="font-semibold text-logigate-primary hover:underline">
               '.$p->cliente->CompanyName.'
            </a>
            '.($p->RefCliente ? '<small>('.$p->RefCliente.')</small>' : '').'
            <br>
            <span class="inline-block mt-1 px-2 py-1 text-xs rounded bg-green-600 text-white">
                <a href="'.route('processos.show',$p->id).'">'.$p->NrProcesso.'</a>
            </span>
        </div>
    ';
}

function estadoBadge($p)
{
    $class =
        $p->Situacao == 'Aberto' ? 'bg-green-600' :
        ($p->Situacao == 'Desembaraçado' ? 'bg-yellow-500' : 'bg-red-600');

    return '
        <span class="px-2 py-1 rounded text-xs text-white '.$class.'">
            '.$p->Estado.'
        </span>
    ';
}

function origemFlag($p)
{
    $flag = strtolower($p->paisOrigem->codigo ?? '');
    return '
        <span class="inline-flex items-center gap-1">
            <span class="flag-icon flag-icon-'.$flag.'"></span>
            '.$p->PortoOrigem.'
        </span>
    ';
}

function valorAduaneiro($p)
{
    return number_format($p->ValorAduaneiro, 2, ',', '.').' kz<br>
        <small>(' . $p->cif . ' ' . $p->Moeda . ')</small>';
}

function dataAbertura($p)
{
    $date = Carbon::parse($p->DataAbertura);
    $weeks = $date->diffInWeeks(Carbon::now());
    $days = $date->diffInDays(Carbon::now());

    return $date->format('d/m/Y').'
        <br><small>
        <span class="text-orange-400">'.$weeks.'</span> Semanas  
        <span class="text-orange-400">'.$days.'</span> dias atrás
        </small>';
}

function statusFactura($p)
{
    if($p->procLicenFaturas->isEmpty())
        return '<span class="text-gray-400 text-xs">Sem Factura</span>';

    $status = ucfirst($p->procLicenFaturas->last()->status_fatura);

    return '
        '.$status.'<br>
        <small>
            <a href="'.route('documentos.show',$p->procLicenFaturas->last()->id).'">
                Ver Factura
            </a>
        </small>
    ';
}

function menuProcessoActions($p)
{
    return '
    <div class="relative" x-data="{open:false}">
        <button @click="open=!open" 
            class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700 text-xs">
            Opções
            <i class="fa fa-chevron-down"></i>
        </button>

        <div x-show="open" @click.away="open=false"
            class="absolute right-0 mt-1 bg-white dark:bg-gray-800 shadow-lg rounded text-sm z-40 w-40">

            <a href="'.route('processos.show',$p->id).'" class="item">👁 Visualizar</a>
            <a href="'.route('processos.edit',$p->id).'" class="item text-yellow-600">✏ Editar</a>
            <a href="'.route('documentos.create',['processo_id'=>$p->id]).'" class="item">📄 Factura</a>
        </div>
    </div>
    ';
}
