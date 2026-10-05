<div class="rounded-lg border border-slate-200 bg-white p-4 text-sm dark:bg-slate-900 dark:text-white">
<p class="font-semibold">Resultado: {{ $result['accepted'] ?? 0 }} linhas aceites · {{ $result['rejected'] ?? 0 }} rejeitadas</p>
<details class="mt-3"><summary class="cursor-pointer">Consultar resultados por linha</summary><ul class="mt-2 space-y-2">
@foreach($result['rows'] ?? [] as $row)<li>Linha {{ $row['line'] }}: {{ $row['status'] === 'accepted' ? 'aceite (ID '.$row['id'].')' : 'rejeitada' }}
@if(isset($row['errors']))<ul>@foreach($row['errors'] as $messages)@foreach((array) $messages as $message)<li class="text-red-600">{{ $message }}</li>@endforeach @endforeach</ul>@endif</li>@endforeach
</ul></details></div>
