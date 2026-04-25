<x-layouts.portal :title="__('Sign In')">
    <div class="mx-auto max-w-sm">
        <h1 class="mb-2 text-2xl font-bold text-white">Sign In</h1>
        <p class="mb-8 text-zinc-400">Access your account to check your subscription status.</p>

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
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
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
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition-colors"
            >Sign In</button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-zinc-500">Don't have an account?
                <a href="{{ route('portal.index') }}" class="text-blue-400 hover:text-blue-300">Buy a plan</a>
                to get started.
            </p>
        </div>
    </div>
</x-layouts.portal>
