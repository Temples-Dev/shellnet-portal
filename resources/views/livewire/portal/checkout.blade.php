<div>
    <div class="mx-auto max-w-lg">
        {{-- Plan summary --}}
        <div class="mb-6 rounded-xl border border-blue-500/30 bg-blue-500/10 p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-blue-400">Selected Plan</p>
            <h2 class="mt-1 text-xl font-bold text-white">{{ $plan->name }}</h2>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">₵{{ $plan->formattedPrice }}</span>
                <span class="text-zinc-400">/ {{ $plan->validityLabel }}</span>
            </div>
        </div>

        @if(session('errors') && session('errors')->has('payment'))
            <div class="mb-4 rounded-lg bg-red-900/30 border border-red-500/30 px-4 py-3 text-sm text-red-400">
                {{ session('errors')->first('payment') }}
            </div>
        @endif

        <form method="POST" action="{{ route('portal.pay', $plan) }}" class="space-y-4">
            @csrf

            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Full Name</label>
                <input
                    type="text"
                    name="fullname"
                    value="{{ old('fullname', $fullname) }}"
                    required
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Your full name"
                    {{ $this->isReturning ? 'readonly' : '' }}
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
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="e.g. 0244123456"
                    {{ $this->isReturning ? 'readonly' : '' }}
                />
                @error('phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Email <span class="text-zinc-500">(optional)</span></label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    class="w-full rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="you@example.com"
                />
                @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <button
                type="submit"
                class="mt-2 w-full rounded-lg bg-blue-600 py-3 text-sm font-semibold text-white hover:bg-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-zinc-950"
            >
                Pay ₵{{ $plan->formattedPrice }} with Paystack
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-zinc-500">
            Your payment is processed securely by Paystack.
            By proceeding you accept our terms of service.
        </p>

        <div class="mt-4 text-center">
            <a href="{{ route('portal.index') }}" class="text-sm text-zinc-500 hover:text-zinc-300">← Back to plans</a>
        </div>
    </div>
</div>
