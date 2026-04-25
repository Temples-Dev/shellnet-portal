<x-layouts.portal :title="__('Buy Data')">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Internet Plans</h1>
        <p class="mt-1 text-zinc-400">Pick a plan and get online in minutes. Payment powered by Paystack.</p>

        @if(session('errors') && session('errors')->has('payment'))
            <div class="mt-4 rounded-lg border border-red-500/30 bg-red-900/20 px-4 py-3 text-sm text-red-400">
                {{ session('errors')->first('payment') }}
            </div>
        @endif
    </div>

    <livewire:portal.plans />
</x-layouts.portal>
