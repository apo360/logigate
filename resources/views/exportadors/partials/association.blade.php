<section class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
<h2 class="text-base font-semibold text-slate-900 dark:text-white">Associação à empresa</h2>
<p class="mt-1 mb-4 text-sm text-slate-500 dark:text-slate-400">Dados utilizados apenas pela sua empresa.</p>
<div class="grid gap-4 sm:grid-cols-2">
@foreach(['codigo_exportador' => 'Código interno', 'additional_info' => 'Informação adicional'] as $field => $label)
<div>
<label for="{{ $field }}" class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ $label }}</label>
<input id="{{ $field }}" name="{{ $field }}" maxlength="{{ $field === 'codigo_exportador' ? 150 : 255 }}" value="{{ old($field, $association?->{$field} ?? '') }}" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500">
@error($field)<p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@endforeach
<div>
<label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
<select id="status" name="status" class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500">
@foreach(['ATIVO' => 'Activo', 'INATIVO' => 'Inactivo'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $association?->status ?? 'ATIVO') === $value)>{{ $label }}</option>@endforeach
</select>@error('status')<p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
</div>
</section>
