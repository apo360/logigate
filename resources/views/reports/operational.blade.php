<!DOCTYPE html><html lang="pt"><head><meta charset="utf-8"><title>{{ $title }}</title>
<style>body{font:10px DejaVu Sans,sans-serif;color:#18304c}h1{font-size:20px}table{border-collapse:collapse;width:100%;table-layout:fixed}td{border-bottom:1px solid #dce5ed;padding:6px;overflow-wrap:break-word}tr:nth-child(even){background:#f3f7fb}</style>
</head><body><h1>{{ $title }}</h1>
@if($truncated)<p>PDF limitado às primeiras 500 linhas, incluindo cabeçalho e resumo. Os totais referem-se a toda a população filtrada. Use CSV ou Excel para o detalhe integral.</p>@endif
@php($columns = max(array_map('count', $rows)))
<table>@foreach($rows as $row)<tr>@for($index=0;$index<$columns;$index++)@php($value=$row[$index]??null)<td>{{ is_bool($value) ? ($value ? 'Sim' : 'Não') : ($value ?? '') }}</td>@endfor</tr>@endforeach</table></body></html>
