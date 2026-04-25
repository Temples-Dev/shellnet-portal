<x-layouts.portal :title="__('Payment Confirmed')">
    <div class="mx-auto max-w-lg text-center">
        <div class="mb-6 flex justify-center">
            <div class="flex size-20 items-center justify-center rounded-full bg-green-500/15">
                <svg class="size-10 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-white">Payment Confirmed!</h1>
        <p class="mt-2 text-zinc-400">Your subscription is being activated. This usually takes a few seconds.</p>

        @if(session('portal_new_password'))
            <div class="mt-8 rounded-xl border border-zinc-700 bg-zinc-900 p-6 text-left">
                <p class="mb-4 text-sm font-medium text-zinc-400">Your account credentials — save these:</p>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-lg bg-zinc-800 px-4 py-3">
                        <span class="text-sm text-zinc-400">Username</span>
                        <span class="font-mono font-semibold text-white">{{ session('portal_username') }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-800 px-4 py-3">
                        <span class="text-sm text-zinc-400">Password</span>
                        <span class="font-mono font-semibold text-white">{{ session('portal_new_password') }}</span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-zinc-500">This password is shown only once. Use it to sign into your account or reconnect if needed.</p>
            </div>
        @endif

        <div class="mt-8 rounded-xl border border-blue-500/20 bg-blue-500/5 p-5">
            <p class="text-sm text-blue-300">
                <strong>What's next?</strong> Your device should be connected automatically within a few seconds.
                If your browser still shows this portal page, try refreshing or visiting any website.
            </p>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('portal.account') }}" class="rounded-lg border border-zinc-700 px-5 py-2.5 text-sm font-medium text-zinc-300 hover:border-zinc-500 hover:text-white transition-colors">
                View My Account
            </a>
            <a href="{{ route('portal.index') }}" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-500 transition-colors">
                Browse Plans
            </a>
        </div>
    </div>
</x-layouts.portal>
