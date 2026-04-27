<x-layouts::portal :title="__('Sign In')">
    <div class="mx-auto max-w-sm">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Sign In</h1>
            <p class="mt-1 text-zinc-400">Check your subscription or renew your plan.</p>
        </div>

        <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
            <form method="POST" action="{{ route('portal.login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Phone Number</label>
                    <input
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        autofocus
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('phone') border-red-500/60 @enderror"
                        placeholder="0244123456"
                    />
                    @error('phone')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Your password"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-zinc-900"
                >Sign In</button>
            </form>
        </div>

        <p class="mt-5 text-center text-sm text-zinc-500">
            New customer?
            <a href="{{ route('portal.register') }}" class="text-blue-400 hover:text-blue-300 transition-colors">Create an account</a>
            to get started.
        </p>
    </div>
</x-layouts::portal>
