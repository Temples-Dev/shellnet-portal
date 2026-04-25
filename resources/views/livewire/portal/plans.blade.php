<div>
    @if(count($this->availableTypes) > 1)
        <div class="mb-6 flex gap-2">
            @foreach($this->availableTypes as $t)
                <button
                    wire:click="$set('type', '{{ $t }}')"
                    class="rounded-full border px-4 py-1.5 text-sm font-medium transition-all {{ $type === $t ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-zinc-700 text-zinc-400 hover:border-zinc-500 hover:text-zinc-200' }}"
                >{{ $t }}</button>
            @endforeach
        </div>
    @endif

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
                            <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400 border border-blue-500/20">{{ $plan->type }}</span>
                        </div>

                        @if($plan->speed_down || $plan->speed_up)
                            <div class="mt-3 flex gap-3 text-xs text-zinc-500">
                                @if($plan->speed_down)
                                    <span class="flex items-center gap-1">
                                        <span class="text-zinc-400">↓</span> {{ $plan->speed_down }} Mbps
                                    </span>
                                @endif
                                @if($plan->speed_up)
                                    <span class="flex items-center gap-1">
                                        <span class="text-zinc-400">↑</span> {{ $plan->speed_up }} Mbps
                                    </span>
                                @endif
                            </div>
                        @endif
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
