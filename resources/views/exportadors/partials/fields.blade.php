@php
    $quick = $quick ?? false;
    $record = $record ?? null;
    $prefix = $quick ? 'quick-exportador-' : 'exportador-';
@endphp
<div class="space-y-6">
<section class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
<h2 class="text-base font-semibold text-slate-900 dark:text-white">Identificação</h2>
<p class="mt-1 mb-4 text-sm text-slate-500 dark:text-slate-400">Nome e identificação fiscal do exportador.</p>
<div class="grid gap-4 sm:grid-cols-2">
<div>
<label for="{{ $prefix }}Exportador" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nome do exportador *</label>
<input id="{{ $prefix }}Exportador" type="text" maxlength="100" required
    @if($quick) wire:model="Exportador" @else name="Exportador" value="{{ old('Exportador', $record?->Exportador) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('Exportador') border-red-500 @enderror" aria-describedby="{{ $prefix }}Exportador-error" @error('Exportador') aria-invalid="true" @enderror>
@error('Exportador')<p id="{{ $prefix }}Exportador-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
<div>
<label for="{{ $prefix }}ExportadorTaxID" class="block text-sm font-medium text-slate-700 dark:text-slate-200">NIF / Tax ID</label>
<input id="{{ $prefix }}ExportadorTaxID" type="text" maxlength="20" 
    @if($quick) wire:model="ExportadorTaxID" @else name="ExportadorTaxID" value="{{ old('ExportadorTaxID', $record?->ExportadorTaxID) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('ExportadorTaxID') border-red-500 @enderror" aria-describedby="{{ $prefix }}ExportadorTaxID-error" @error('ExportadorTaxID') aria-invalid="true" @enderror>
@error('ExportadorTaxID')<p id="{{ $prefix }}ExportadorTaxID-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@unless($quick)
<div>
<label for="{{ $prefix }}AccountID" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Conta contabilística</label>
<input id="{{ $prefix }}AccountID" type="text" maxlength="30" 
    @if($quick) wire:model="AccountID" @else name="AccountID" value="{{ old('AccountID', $record?->AccountID) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('AccountID') border-red-500 @enderror" aria-describedby="{{ $prefix }}AccountID-error" @error('AccountID') aria-invalid="true" @enderror>
@error('AccountID')<p id="{{ $prefix }}AccountID-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@endunless
</div>
</section>
<section class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
<h2 class="text-base font-semibold text-slate-900 dark:text-white">Localização e contactos</h2>
<p class="mt-1 mb-4 text-sm text-slate-500 dark:text-slate-400">País, endereço e meios de contacto.</p>
<div class="grid gap-4 sm:grid-cols-2">
<div>
<label for="{{ $prefix }}Endereco" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Endereço</label>
<input id="{{ $prefix }}Endereco" type="text" maxlength="254" 
    @if($quick) wire:model="Endereco" @else name="Endereco" value="{{ old('Endereco', $record?->Endereco) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('Endereco') border-red-500 @enderror" aria-describedby="{{ $prefix }}Endereco-error" @error('Endereco') aria-invalid="true" @enderror>
@error('Endereco')<p id="{{ $prefix }}Endereco-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@unless($quick)
<div>
<label for="{{ $prefix }}Cidade" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Cidade</label>
<input id="{{ $prefix }}Cidade" type="text" maxlength="60" 
    @if($quick) wire:model="Cidade" @else name="Cidade" value="{{ old('Cidade', $record?->Cidade) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('Cidade') border-red-500 @enderror" aria-describedby="{{ $prefix }}Cidade-error" @error('Cidade') aria-invalid="true" @enderror>
@error('Cidade')<p id="{{ $prefix }}Cidade-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@endunless
<div>
<label for="{{ $prefix }}Telefone" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Telefone</label>
<input id="{{ $prefix }}Telefone" type="tel" maxlength="20" 
    @if($quick) wire:model="Telefone" @else name="Telefone" value="{{ old('Telefone', $record?->Telefone) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('Telefone') border-red-500 @enderror" aria-describedby="{{ $prefix }}Telefone-error" @error('Telefone') aria-invalid="true" @enderror>
@error('Telefone')<p id="{{ $prefix }}Telefone-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
<div>
<label for="{{ $prefix }}Email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
<input id="{{ $prefix }}Email" type="email" maxlength="254" 
    @if($quick) wire:model="Email" @else name="Email" value="{{ old('Email', $record?->Email) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('Email') border-red-500 @enderror" aria-describedby="{{ $prefix }}Email-error" @error('Email') aria-invalid="true" @enderror>
@error('Email')<p id="{{ $prefix }}Email-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@unless($quick)
<div>
<label for="{{ $prefix }}Website" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Website</label>
<input id="{{ $prefix }}Website" type="url" maxlength="60" 
    @if($quick) wire:model="Website" @else name="Website" value="{{ old('Website', $record?->Website) }}" @endif
    class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 @error('Website') border-red-500 @enderror" aria-describedby="{{ $prefix }}Website-error" @error('Website') aria-invalid="true" @enderror>
@error('Website')<p id="{{ $prefix }}Website-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
@endunless
<div>
<label for="{{ $prefix }}Pais" class="block text-sm font-medium text-slate-700 dark:text-slate-200">País *</label>
<select id="{{ $prefix }}Pais" required @if($quick) wire:model="Pais" @else name="Pais" @endif class="mt-1 block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" aria-describedby="{{ $prefix }}Pais-error" @error('Pais') aria-invalid="true" @enderror>
<option value="">Selecione um país</option>
@foreach($paises as $pais)<option value="{{ $pais->id }}" @unless($quick) @selected((string) old('Pais', $record?->Pais) === (string) $pais->id) @endunless>{{ $pais->pais }}</option>@endforeach
</select>@error('Pais')<p id="{{ $prefix }}Pais-error" class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror</div>
</div>
</section>
</div>
