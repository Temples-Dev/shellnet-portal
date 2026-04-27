<x-layouts::portal :title="__('Payment Confirmed')">
    <div class="mx-auto max-w-lg">
        {{-- Success header --}}
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-green-500/10 ring-1 ring-green-500/20">
                <svg class="size-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Payment Confirmed</h1>
            <p class="mt-2 text-zinc-400">Your subscription is now active.</p>
        </div>

        {{-- Connection instructions --}}
        <div class="mb-4 rounded-xl border border-blue-500/20 bg-blue-500/5 px-5 py-4">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 size-5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-300">How to connect</p>
                    <p class="mt-1 text-sm text-blue-300/70">Connect your device to the WiFi network, open any website and you will be redirected to the hotspot login page. Enter your username and password from your account.</p>
                </div>
            </div>
        </div>

        {{-- Credentials reminder --}}
        <div class="mb-6 rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
            <p class="text-sm text-zinc-400">
                Your hotspot credentials (username &amp; password) are saved in your
                <a href="{{ route('portal.account') }}" class="text-blue-400 hover:text-blue-300 transition-colors">account page</a>
                — you can view them any time.
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('portal.account') }}"
               class="flex-1 rounded-lg border border-zinc-700 py-2.5 text-center text-sm font-medium text-zinc-300 transition-colors hover:border-zinc-500 hover:text-white">
                My Account
            </a>
            <a href="{{ route('portal.index') }}"
               class="flex-1 rounded-lg bg-blue-600 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-blue-500">
                Browse Plans
            </a>
        </div>
    </div>
</x-layouts::portal>
