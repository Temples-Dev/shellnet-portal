<div>
    @if($this->plans->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-800 py-20 text-center">
            <flux:icon.signal class="mx-auto mb-3 size-10 text-zinc-700" />
            <p class="text-zinc-500">No plans available right now. Check back soon.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($this->plans as $plan)
                <div class="group relative flex flex-col overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900 transition-all hover:border-blue-500/50 hover:shadow-lg hover:shadow-blue-500/5">
                    <div class="flex-1 p-5">
                        <div class="mb-3 flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-white">{{ $plan->name }}</h3>
                                <p class="mt-0.5 text-xs text-zinc-500">{{ $plan->validityLabel }}</p>
                            </div>
                            <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400 border border-blue-500/20">Hotspot</span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($plan->speed_down || $plan->speed_up)
                                <span class="flex items-center gap-1 rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-400">
                                    <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"/></svg>
                                    @if($plan->speed_down && $plan->speed_up)
                                        {{ $plan->speed_down }}↓ / {{ $plan->speed_up }}↑ Mbps
                                    @elseif($plan->speed_down)
                                        {{ $plan->speed_down }} Mbps
                                    @else
                                        {{ $plan->speed_up }} Mbps
                                    @endif
                                </span>
                            @endif

                            @if(($plan->shared_users ?? 1) === 1)
                                <span class="flex items-center gap-1 rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-400">
                                    <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                                    1 Device only
                                </span>
                            @else
                                <span class="flex items-center gap-1 rounded-md bg-blue-500/10 px-2 py-1 text-xs text-blue-400 border border-blue-500/20">
                                    <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/></svg>
                                    Up to {{ $plan->shared_users }} Devices
                                </span>
                            @endif

                            <span class="flex items-center gap-1 rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-400">
                                <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                Unlimited data
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-zinc-800 px-5 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-2xl font-bold tracking-tight text-white">₵{{ $plan->formattedPrice }}</span>
                            </div>
                            <a
                                href="{{ route('portal.checkout', $plan) }}"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-zinc-900"
                            >Buy Now</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
