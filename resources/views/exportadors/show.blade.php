<x-app-layout>

    <x-breadcrumb :items="[

        ['name' => 'Exportadores', 'url' => route('exportadors.index')],

        ['name' => $exportador->Exportador, 'url' => route('exportadors.show', $exportador->id)]

    ]" separator="/" />

    <div class="max-w-5xl mx-auto py-6 space-y-6">

        @if(session('success'))<p role="status" class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</p>@endif

        <div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 text-xl font-bold">{{ mb_strtoupper(mb_substr($exportador->Exportador, 0, 2)) }}</span>
<div>
<h1 class="text-2xl font-bold">{{ $exportador->Exportador }}</h1>
<p class="mt-1 text-sm text-blue-100">{{ $exportador->ExportadorTaxID ?: 'NIF / Tax ID não indicado' }}</p>
</div>
</div>
<div class="flex gap-2">
<a class="rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold hover:bg-white/25" href="{{ route('exportadors.index') }}">Voltar à lista</a>@can('update', $exportador)<a class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50" href="{{ route('exportadors.edit', $exportador->id) }}">Editar</a>@endcan</div>
</div>
        </div>
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">

            <h2 class="text-lg font-semibold mb-4">Dados do cadastro</h2>

            <dl class="grid md:grid-cols-2 gap-4">

                @foreach(['ExportadorID' => 'Código', 'ExportadorTaxID' => 'NIF / Tax ID', 'AccountID' => 'Conta', 'Endereco' => 'Endereço', 'Cidade' => 'Cidade', 'Telefone' => 'Telefone', 'Email' => 'Email', 'Website' => 'Website'] as $field => $label)

                    <div>
<dt class="text-slate-500 dark:text-slate-400">{{ $label }}</dt>
<dd class="mt-1 break-words">@if($field === 'Email' && $exportador->Email)<a href="mailto:{{ $exportador->Email }}" class="text-blue-600 hover:underline">{{ $exportador->Email }}</a>@else{{ $exportador->{$field} ?: '—' }}@endif</dd>
</div>

                @endforeach

                <div>
<dt class="text-slate-500 dark:text-slate-400">País</dt>
<dd>{{ $paisNome ?? '—' }}</dd>
</div>

            </dl>

        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">

            <h2 class="text-lg font-semibold mb-4">Associação à empresa</h2>

            <dl class="grid md:grid-cols-2 gap-4">

                <div>
<dt>Estado</dt>
<dd>{{ $association->status === 'ATIVO' ? 'Activo' : 'Inactivo' }}</dd>
</div>

                <div>
<dt>Código interno</dt>
<dd>{{ $association->codigo_exportador ?: '—' }}</dd>
</div>

                <div>
<dt>Informação adicional</dt>
<dd>{{ $association->additional_info ?: '—' }}</dd>
</div>

                <div>
<dt>Data de associação</dt>
<dd>{{ $association->data_associacao ? \Carbon\Carbon::parse($association->data_associacao)->format('d/m/Y') : '—' }}</dd>
</div>

            </dl>

        </section>

        <section class="grid grid-cols-2 gap-4">

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
<p>Processos desta empresa</p>
<strong class="text-2xl">{{ $processosCount }}</strong>
</div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
<p>Licenciamentos desta empresa</p>
<strong class="text-2xl">{{ $licenciamentosCount }}</strong>
</div>

        </section>

        <a class="text-blue-600" href="{{ route('exportadors.index') }}">Voltar à lista</a>

    </div>

</x-app-layout>
