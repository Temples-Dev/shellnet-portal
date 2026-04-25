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
            <p class="mt-2 text-zinc-400">Your subscription is being activated. This usually takes a few seconds.</p>
        </div>

        @if(session('portal_new_credentials'))
            @php $creds = session('portal_new_credentials'); @endphp

            {{-- WiFi credentials --}}
            <div class="mb-4 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900">
                <div class="border-b border-zinc-800 bg-zinc-800/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <flux:icon.wifi class="size-4 text-blue-400" />
                        <p class="text-sm font-semibold text-white">WiFi Hotspot Credentials</p>
                    </div>
                    <p class="mt-0.5 text-xs text-zinc-500">Use these to connect on the hotspot login page</p>
                </div>
                <div class="divide-y divide-zinc-800">
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-zinc-400">Username</span>
                        <span class="font-mono text-sm font-semibold text-white">{{ $creds['username'] }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-zinc-400">Password</span>
                        <span class="font-mono text-sm font-semibold text-white">{{ $creds['password'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Portal login reminder --}}
            <div class="mb-6 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900">
                <div class="border-b border-zinc-800 bg-zinc-800/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <flux:icon.user class="size-4 text-zinc-400" />
                        <p class="text-sm font-semibold text-white">ShellNet Portal Login</p>
                    </div>
                    <p class="mt-0.5 text-xs text-zinc-500">To check your subscription or renew</p>
                </div>
                <div class="divide-y divide-zinc-800">
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-zinc-400">Phone</span>
                        <span class="font-mono text-sm font-semibold text-white">{{ $creds['phone'] }}</span>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <span class="text-sm text-zinc-400">Password</span>
                        <span class="font-mono text-sm font-semibold text-white">{{ $creds['password'] }}</span>
                    </div>
                </div>
            </div>

            <div class="mb-6 rounded-lg border border-amber-500/20 bg-amber-500/5 px-4 py-3">
                <p class="text-xs text-amber-400">Save your credentials now — the password is shown only once.</p>
            </div>
        @else
            <div class="mb-6 rounded-xl border border-zinc-800 bg-zinc-900 px-5 py-4">
                <p class="text-sm text-zinc-400">Your existing account has been renewed. Use your phone number and password to sign in.</p>
            </div>
        @endif

        {{-- Status note --}}
        <div class="mb-6 rounded-xl border border-blue-500/20 bg-blue-500/5 px-5 py-4">
            <p class="text-sm text-blue-300">
                Your device should connect automatically within a few seconds. If not, open your browser and try visiting any website — you may be prompted to enter your WiFi credentials.
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('portal.account') }}" class="flex-1 rounded-lg border border-zinc-700 py-2.5 text-center text-sm font-medium text-zinc-300 transition-colors hover:border-zinc-500 hover:text-white">
                My Account
            </a>
            <a href="{{ route('portal.index') }}" class="flex-1 rounded-lg bg-blue-600 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-blue-500">
                Browse Plans
            </a>
        </div>
    </div>
</x-layouts::portal>
