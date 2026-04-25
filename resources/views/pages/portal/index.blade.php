<x-layouts::portal :title="__('Internet Plans')">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Internet Plans</h1>
        <p class="mt-1 text-zinc-400">Choose a plan and get online in minutes.</p>

        @if($errors->has('payment'))
            <div class="mt-4 rounded-lg border border-red-500/20 bg-red-950/30 px-4 py-3 text-sm text-red-400">
                {{ $errors->first('payment') }}
            </div>
        @endif
    </div>

    <livewire:portal.plans />
</x-layouts::portal>
