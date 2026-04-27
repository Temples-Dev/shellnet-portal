<div class="space-y-6">
    @if(session('portal_processing'))
        <div class="flex items-start gap-3 rounded-xl border border-blue-500/20 bg-blue-500/5 px-5 py-4">
            <svg class="mt-0.5 size-5 shrink-0 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-300">Payment received — activating subscription</p>
                <p class="mt-0.5 text-xs text-blue-300/70">This usually completes within a minute. Refresh the page if your plan hasn't appeared.</p>
            </div>
        </div>
    @endif

    {{-- Hotspot login credentials --}}
    <div class="rounded-xl border border-blue-500/20 bg-zinc-900 p-5">
        <div class="mb-4 flex items-center gap-2">
            <svg class="size-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.14 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
            </svg>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-zinc-400">Hotspot Login</h3>
        </div>
        <div class="space-y-3">
            {{-- Username row --}}
            <div
                x-data="{ copied: false }"
                class="flex items-center justify-between rounded-lg bg-zinc-800/60 px-4 py-2.5"
            >
                <span class="text-sm text-zinc-400">Username</span>
                <div class="flex items-center gap-2">
                    <span class="select-all font-mono text-sm font-medium text-white">{{ $this->customer->username }}</span>
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText('{{ $this->customer->username }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="text-zinc-500 hover:text-zinc-300 transition-colors"
                        :aria-label="copied ? 'Copied' : 'Copy username'"
                    >
                        <svg x-show="!copied" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg x-show="copied" x-cloak class="size-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Password row --}}
            @php $plainPassword = $this->customer->pppoe_password ?? $this->customer->username; @endphp
            <div
                x-data="{ show: false, copied: false }"
                class="flex items-center justify-between rounded-lg bg-zinc-800/60 px-4 py-2.5"
            >
                <span class="text-sm text-zinc-400">Password</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-medium text-white">
                        <span x-show="!show" class="tracking-widest text-zinc-500">••••••••</span>
                        <span x-show="show" x-cloak class="select-all">{{ $plainPassword }}</span>
                    </span>
                    {{-- Show/hide --}}
                    <button
                        type="button"
                        x-on:click="show = !show"
                        class="text-zinc-500 hover:text-zinc-300 transition-colors"
                        :aria-label="show ? 'Hide password' : 'Show password'"
                    >
                        <svg x-show="!show" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" x-cloak class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                    {{-- Copy --}}
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText('{{ $plainPassword }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                        class="text-zinc-500 hover:text-zinc-300 transition-colors"
                        :aria-label="copied ? 'Copied' : 'Copy password'"
                    >
                        <svg x-show="!copied" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg x-show="copied" x-cloak class="size-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <p class="mt-3 text-xs text-zinc-600">Enter these credentials on the WiFi login page to connect.</p>
    </div>

    {{-- Active subscription --}}
    <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-400">Active Subscription</h3>

        @if($this->activeSubscription)
            @php $sub = $this->activeSubscription; @endphp
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-400">Plan</span>
                    <span class="font-semibold text-white">{{ $sub->plan_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-400">Router</span>
                    <span class="text-sm text-zinc-300">{{ $sub->router_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-zinc-400">Expires</span>
                    <span class="text-sm text-zinc-300">{{ $sub->expires_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>
                @if($sub->expires_at)
                    @php
                        $totalSecs = max(0, now()->diffInSeconds($sub->expires_at, false));
                        $urgent    = $totalSecs < 172800; // < 2 days
                    @endphp
                    <div
                        x-data="{
                            ends: {{ $sub->expires_at->timestamp }},
                            remaining: {{ max(0, $totalSecs) }},
                            init() {
                                this.tick();
                                setInterval(() => this.tick(), 1000);
                            },
                            tick() {
                                this.remaining = Math.max(0, this.ends - Math.floor(Date.now() / 1000));
                            },
                            pad(n) { return String(n).padStart(2, '0'); },
                            days()    { return Math.floor(this.remaining / 86400); },
                            hours()   { return Math.floor((this.remaining % 86400) / 3600); },
                            minutes() { return Math.floor((this.remaining % 3600) / 60); },
                            seconds() { return this.remaining % 60; },
                        }"
                        class="space-y-2"
                    >
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-zinc-400">Time Remaining</span>
                            <div class="{{ $urgent ? 'text-red-400' : 'text-green-400' }} font-mono text-sm font-semibold tabular-nums">
                                <span x-text="days()"></span>d
                                <span x-text="pad(hours())"></span>h
                                <span x-text="pad(minutes())"></span>m
                                <span x-text="pad(seconds())"></span>s
                            </div>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-zinc-700">
                            @php
                                $planValidity = $sub->plan?->validity ?? 30;
                                $unit         = $sub->plan?->validity_unit ?? 'Days';
                                $totalPlanSecs = match($unit) {
                                    'Mins'   => $planValidity * 60,
                                    'Hrs'    => $planValidity * 3600,
                                    'Months' => $planValidity * 30 * 86400,
                                    default  => $planValidity * 86400,
                                };
                                $pct = min(100, max(0, $totalPlanSecs > 0 ? round(($totalSecs / $totalPlanSecs) * 100) : 0));
                            @endphp
                            <div
                                class="h-1.5 rounded-full transition-all {{ $urgent ? 'bg-red-500' : 'bg-green-500' }}"
                                style="width: {{ $pct }}%"
                            ></div>
                        </div>
                    </div>
                @endif
            </div>

            <a
                href="{{ route('portal.index') }}"
                class="mt-5 block w-full rounded-lg border border-blue-500/30 py-2.5 text-center text-sm font-medium text-blue-400 hover:bg-blue-500/10 transition-colors"
            >Renew / Buy Another Plan</a>
        @else
            <div class="py-8 text-center">
                <p class="text-zinc-500">No active subscription.</p>
                <a
                    href="{{ route('portal.index') }}"
                    class="mt-4 inline-block rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-500 transition-colors"
                >Browse Plans</a>
            </div>
        @endif
    </div>

    {{-- Payment Transactions --}}
    @if($this->paymentTransactions->isNotEmpty())
        <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-400">Payment History</h3>

            @if($errors->has('payment'))
                <div class="mb-3 rounded-lg border border-red-500/20 bg-red-950/30 px-4 py-2.5 text-sm text-red-400">
                    {{ $errors->first('payment') }}
                </div>
            @endif

            <div class="space-y-2">
                @foreach($this->paymentTransactions as $tx)
                    @php
                        $txStatus    = (int) $tx->status;
                        $statusLabel = $tx->status_label;
                        $statusClass = match($txStatus) {
                            2 => 'bg-green-500/15 text-green-400',
                            3 => 'bg-red-500/15 text-red-400',
                            4 => 'bg-zinc-700 text-zinc-400',
                            default => 'bg-amber-500/15 text-amber-400',
                        };
                    @endphp
                    <div class="flex items-center justify-between rounded-lg bg-zinc-800/50 px-4 py-2.5 gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ $tx->plan_name }}</p>
                            <p class="text-xs text-zinc-500">
                                ₵{{ number_format((float) $tx->price, 2) }}
                                · {{ $tx->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            @if($txStatus === 3)
                                {{-- Failed: start fresh Paystack session --}}
                                <form method="POST" action="{{ route('portal.payment.retry', $tx) }}"
                                      x-data="{ sent: false }" @submit.prevent="sent = true; $el.submit()">
                                    @csrf
                                    <button type="submit" x-show="!sent"
                                        class="rounded-lg bg-blue-600/20 border border-blue-500/30 px-2.5 py-1 text-xs font-medium text-blue-400 hover:bg-blue-600/30 transition-colors">
                                        Retry
                                    </button>
                                    <span x-show="sent" x-cloak class="text-xs text-zinc-500">Redirecting…</span>
                                </form>
                            @elseif($txStatus === 1)
                                {{-- Unpaid: re-verify with Paystack (pg_url may be expired) --}}
                                <form method="POST" action="{{ route('portal.payment.resume', $tx) }}"
                                      x-data="{ sent: false }" @submit.prevent="sent = true; $el.submit()">
                                    @csrf
                                    <button type="submit" x-show="!sent"
                                        class="rounded-lg bg-amber-500/20 border border-amber-500/30 px-2.5 py-1 text-xs font-medium text-amber-400 hover:bg-amber-500/30 transition-colors">
                                        Verify
                                    </button>
                                    <span x-show="sent" x-cloak class="text-xs text-zinc-500">Checking…</span>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
