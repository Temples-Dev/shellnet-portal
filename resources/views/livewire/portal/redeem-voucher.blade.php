<div class="mx-auto max-w-md">

    <div class="mb-6 text-center">
        <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-blue-500/10">
            <svg class="size-7 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-white">Redeem a Voucher</h2>
        <p class="mt-1 text-sm text-zinc-400">Enter the code from your voucher card to activate your plan.</p>
    </div>

    <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
        <form wire:submit="redeem" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Voucher Code</label>
                <input
                    type="text"
                    wire:model="code"
                    placeholder="e.g. HOTSPOT-ABCD1234"
                    autofocus
                    class="w-full rounded-lg border {{ $errors->has('code') ? 'border-red-500 bg-red-950/20' : 'border-zinc-700 bg-zinc-800' }} px-4 py-3 text-center font-mono text-lg font-semibold uppercase tracking-widest text-white placeholder-zinc-600 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                />
                @error('code')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-zinc-900 disabled:opacity-60"
            >
                <span wire:loading.remove>Activate Voucher</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Activating…
                </span>
            </button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('portal.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">
            ← Back to plans
        </a>
    </div>
</div>
