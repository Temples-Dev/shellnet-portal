<div class="space-y-6">
    {{-- Customer info --}}
    <div class="flex items-center gap-4 rounded-xl border border-zinc-800 bg-zinc-900 p-5">
        <div class="flex size-12 items-center justify-center rounded-full bg-blue-500/15 text-lg font-bold text-blue-400">
            {{ strtoupper(substr($this->customer->fullname, 0, 1)) }}
        </div>
        <div>
            <p class="font-semibold text-white">{{ $this->customer->fullname }}</p>
            <p class="text-sm text-zinc-400 font-mono">{{ $this->customer->username }}</p>
            <p class="text-sm text-zinc-500">{{ $this->customer->phone }}</p>
        </div>
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
                    @php $days = max(0, (int) now()->diffInDays($sub->expires_at, false)); @endphp
                    <div class="flex justify-between">
                        <span class="text-sm text-zinc-400">Days Remaining</span>
                        <span class="text-sm font-semibold {{ $days <= 2 ? 'text-red-400' : 'text-green-400' }}">{{ $days }} days</span>
                    </div>
                    <div class="mt-2">
                        @php $pct = min(100, max(0, $days > 0 ? round(($days / 30) * 100) : 0)); @endphp
                        <div class="h-1.5 w-full rounded-full bg-zinc-700">
                            <div class="h-1.5 rounded-full {{ $days <= 2 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $pct }}%"></div>
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

    {{-- Recent history --}}
    @if($this->recentSubscriptions->isNotEmpty())
        <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-zinc-400">History</h3>
            <div class="space-y-2">
                @foreach($this->recentSubscriptions as $sub)
                    <div class="flex items-center justify-between rounded-lg bg-zinc-800/50 px-4 py-2.5">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $sub->plan_name }}</p>
                            <p class="text-xs text-zinc-500">{{ $sub->recharged_at?->format('d M Y') }}</p>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $sub->status === 'on' ? 'bg-green-500/15 text-green-400' : 'bg-zinc-700 text-zinc-400' }}">
                            {{ $sub->status === 'on' ? 'Active' : 'Expired' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
