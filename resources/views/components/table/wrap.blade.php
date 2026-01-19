@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @if($title || $subtitle || isset($toolbar))
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                @if($title)
                    <h2 class="text-sm md:text-base font-semibold text-slate-50 tracking-tight">
                        {{ $title }}
                    </h2>
                @endif
                @if($subtitle)
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
            @if(isset($toolbar))
                <div class="flex flex-wrap items-center gap-2">
                    {{ $toolbar }}
                </div>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-950/60">
        <table class="min-w-full text-left text-xs text-slate-200">
            <thead class="bg-slate-950/80 border-b border-slate-800 text-[11px] uppercase tracking-wide text-black">
                {{ $head ?? '' }}
            </thead>
            <tbody class="divide-y divide-slate-900/80">
                {{ $body ?? $slot }}
            </tbody>
        </table>
    </div>

    @if(isset($footer))
        <div class="pt-2">
            {{ $footer }}
        </div>
    @endif
</div>
