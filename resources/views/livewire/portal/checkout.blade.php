<div class="mx-auto max-w-md">
    {{-- Plan summary card --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900">
        <div class="border-b border-zinc-800 px-5 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Order Summary</p>
        </div>
        <div class="flex items-center justify-between px-5 py-4">
            <div>
                <p class="font-semibold text-white">{{ $plan->name }}</p>
                <p class="text-sm text-zinc-400">{{ $plan->validityLabel }} · {{ $plan->type }}</p>
            </div>
            <p class="text-xl font-bold text-white">₵{{ $plan->formattedPrice }}</p>
        </div>
    </div>

    @if($errors->has('payment'))
        <div class="mb-4 rounded-lg border border-red-500/20 bg-red-950/30 px-4 py-3 text-sm text-red-400">
            {{ $errors->first('payment') }}
        </div>
    @endif

    <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
        <h2 class="mb-5 text-sm font-semibold uppercase tracking-wide text-zinc-400">Your Details</h2>

        <form method="POST" action="{{ route('portal.pay', $plan) }}" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Full Name</label>
                <input
                    type="text"
                    name="fullname"
                    value="{{ old('fullname', $fullname) }}"
                    required
                    {{ $this->isReturning ? 'readonly' : '' }}
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 read-only:opacity-60"
                    placeholder="Your full name"
                />
                @error('fullname') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Phone Number</label>
                <input
                    type="tel"
                    name="phone"
                    value="{{ old('phone', $phone) }}"
                    required
                    {{ $this->isReturning ? 'readonly' : '' }}
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 read-only:opacity-60"
                    placeholder="0244123456"
                />
                @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">
                    Email <span class="text-zinc-600">(optional — for receipts)</span>
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-600 transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="you@example.com"
                />
            </div>

            <button
                type="submit"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-zinc-900"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Pay ₵{{ $plan->formattedPrice }} securely
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-zinc-600">Payments processed securely by Paystack</p>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('portal.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300 transition-colors">
            ← Back to plans
        </a>
    </div>
</div>
