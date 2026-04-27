<div class="mx-auto max-w-md">
    {{-- Order summary --}}
    <div class="mb-6 overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900">
        <div class="border-b border-zinc-800 px-5 py-3">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">Order Summary</p>
        </div>
        <div class="space-y-2 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-white">{{ $plan->name }}</p>
                    <p class="text-sm text-zinc-400">{{ $plan->validityLabel }} · Hotspot</p>
                </div>
                <p class="text-xl font-bold text-white">₵{{ $plan->formattedPrice }}</p>
            </div>
            @if($discountAmount > 0)
                <div class="flex items-center justify-between border-t border-zinc-800 pt-2">
                    <span class="text-sm text-green-400">Coupon discount</span>
                    <span class="text-sm font-medium text-green-400">− ₵{{ number_format($discountAmount, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-white">Total</span>
                    <span class="text-xl font-bold text-white">₵{{ number_format($this->finalPrice, 2) }}</span>
                </div>
            @endif
        </div>
    </div>

    @if($errors->has('payment'))
        <div class="mb-4 rounded-lg border border-red-500/20 bg-red-950/30 px-4 py-3 text-sm text-red-400">
            {{ $errors->first('payment') }}
        </div>
    @endif

    @php $action = $this->subscriptionAction; @endphp

    @if($action === 'extend')
        <div class="mb-4 flex items-start gap-3 rounded-xl border border-blue-500/20 bg-blue-500/5 px-4 py-3">
            <svg class="mt-0.5 size-4 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-300">Extends your current plan</p>
                <p class="mt-0.5 text-xs text-blue-300/70">
                    Your <strong>{{ $this->activeSubscription->plan_name }}</strong> will be extended.
                    New expiry: <strong>{{ $this->extendedExpiry }}</strong>
                </p>
            </div>
        </div>

    @elseif($action === 'upgrade')
        <div class="mb-4 flex items-start gap-3 rounded-xl border border-amber-500/20 bg-amber-500/5 px-4 py-3">
            <svg class="mt-0.5 size-4 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-300">Upgrading your plan</p>
                <p class="mt-0.5 text-xs text-amber-300/70">
                    Your current <strong>{{ $this->activeSubscription->plan_name }}</strong> will end immediately.
                    Remaining time will be lost.
                </p>
            </div>
        </div>

    @elseif($action === 'downgrade')
        <div class="mb-4 flex items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/5 px-4 py-3">
            <svg class="mt-0.5 size-4 shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            <div>
                <p class="text-sm font-medium text-red-300">Cannot downgrade</p>
                <p class="mt-0.5 text-xs text-red-300/70">
                    You are on <strong>{{ $this->activeSubscription->plan_name }}</strong>.
                    Please choose a plan equal to or higher in value.
                </p>
            </div>
        </div>
    @endif

    {{-- Paying as --}}
    @php $customer = auth('customer')->user(); @endphp
    <div class="mb-4 flex items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-900 px-5 py-3.5">
        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-blue-500/15 text-sm font-bold text-blue-400">
            {{ strtoupper(substr($customer->fullname, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="text-sm font-medium text-white">{{ $customer->fullname }}</p>
            <p class="truncate text-xs text-zinc-500 font-mono">{{ $customer->username }}</p>
        </div>
        <a href="{{ route('portal.account') }}" class="ml-auto shrink-0 text-xs text-zinc-500 hover:text-zinc-300 transition-colors">
            Not you?
        </a>
    </div>

    <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6">
        <form method="POST" action="{{ route('portal.pay', $plan) }}" class="space-y-4">
            @csrf

            {{-- Coupon code --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-zinc-300">
                    Coupon Code <span class="text-zinc-600">(optional)</span>
                </label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        name="coupon_code"
                        wire:model="coupon_code"
                        placeholder="Enter code"
                        @if($couponValid) readonly @endif
                        class="flex-1 rounded-lg border {{ $couponValid ? 'border-green-600 bg-green-950/30' : 'border-zinc-700 bg-zinc-800' }} px-3 py-2.5 text-sm text-white placeholder-zinc-600 uppercase tracking-wider transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 read-only:opacity-70"
                    />
                    @if($couponValid)
                        <button type="button" wire:click="removeCoupon"
                            class="rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-sm text-zinc-400 hover:text-white transition-colors">
                            Remove
                        </button>
                    @else
                        <button type="button" wire:click="applyCoupon"
                            class="rounded-lg border border-blue-500/50 bg-blue-600/20 px-4 py-2.5 text-sm font-medium text-blue-300 hover:bg-blue-600/40 hover:text-white transition-colors">
                            Apply
                        </button>
                    @endif
                </div>
                @if($couponMessage)
                    <p class="mt-1.5 text-xs {{ $couponValid ? 'text-green-400' : 'text-red-400' }}">{{ $couponMessage }}</p>
                @endif
            </div>

            <button
                type="submit"
                @if($action === 'downgrade') disabled @endif
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-zinc-900 {{ $action === 'downgrade' ? 'bg-zinc-700 cursor-not-allowed opacity-50' : 'bg-blue-600 hover:bg-blue-500 focus:ring-blue-500' }}"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Pay ₵{{ number_format($this->finalPrice, 2) }} securely
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
