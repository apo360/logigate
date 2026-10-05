<x-app-layout>
<div class="mx-auto max-w-5xl space-y-6 py-6">
<div class="rounded-xl bg-gradient-to-r from-blue-600 to-indigo-700 p-6 text-white">
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
<div>
<h1 class="text-2xl font-bold">Novo exportador</h1>
<p class="mt-1 text-sm text-blue-100">SUBNovo exportador</p>
</div>
<a href="{{ route('exportadors.index') }}" class="rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold hover:bg-white/25">Voltar à lista</a>
</div>
</div>@if(session('success'))<div role="status" class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">Não foi possível guardar. Verifique os campos indicados.@error('escopo')<p>{{ $message }}</p>@enderror</div>@endif<form action="{{ route('exportadors.store') }}" method="POST" class="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900" x-data="{ saving: false }" @submit="saving = true">@csrf
<p class="text-sm text-slate-500">Os campos com * são obrigatórios. Se o exportador já existir, será associado à sua empresa.</p>
@include('exportadors.partials.fields')
@include('exportadors.partials.association', ['association' => null])
<div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
<a href="{{ route('exportadors.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</a>
<button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50" :disabled="saving" x-text="saving ? 'A guardar…' : 'Guardar'">Guardar</button>
</div>
</form>
</div>
</x-app-layout>
