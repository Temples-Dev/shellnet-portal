<x-layouts::portal :title="__('Create Account')">
    <div class="mx-auto max-w-sm">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Create Account</h1>
            <p class="mt-1 text-zinc-400">Sign up to buy and manage your internet plans.</p>
        </div>

        <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
            <form method="POST" action="{{ route('portal.register.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Full Name</label>
                    <input
                        type="text"
                        name="fullname"
                        value="{{ old('fullname') }}"
                        required
                        autofocus
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('fullname') border-red-500/60 @enderror"
                        placeholder="John Doe"
                    />
                    @error('fullname')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Phone Number</label>
                    <input
                        type="tel"
                        name="phone"
                        value="{{ old('phone') }}"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('phone') border-red-500/60 @enderror"
                        placeholder="0244123456"
                    />
                    @error('phone')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">
                        Email <span class="font-normal text-zinc-600">(optional)</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('email') border-red-500/60 @enderror"
                        placeholder="you@example.com"
                    />
                    @error('email')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('password') border-red-500/60 @enderror"
                        placeholder="Min 6 characters"
                    />
                    @error('password')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Repeat password"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-zinc-900"
                >Create Account</button>
            </form>
        </div>

        <p class="mt-5 text-center text-sm text-zinc-500">
            Already have an account?
            <a href="{{ route('portal.login') }}" class="text-blue-400 hover:text-blue-300 transition-colors">Sign in</a>
        </p>
    </div>
</x-layouts::portal>
