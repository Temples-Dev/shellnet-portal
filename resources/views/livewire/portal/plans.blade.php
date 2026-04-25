<div>
    @if(count($this->availableTypes) > 1)
        <div class="mb-6 flex gap-2">
            @foreach($this->availableTypes as $t)
                <button
                    wire:click="$set('type', '{{ $t }}')"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors {{ $type === $t ? 'bg-blue-600 text-white' : 'bg-zinc-800 text-zinc-400 hover:bg-zinc-700 hover:text-white' }}"
                >{{ $t }}</button>
            @endforeach
        </div>
    @endif

    @if($this->plans->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-700 py-16 text-center text-zinc-500">
            No plans available at the moment. Please check back soon.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($this->plans as $plan)
                <div class="flex flex-col rounded-xl border border-zinc-800 bg-zinc-900 p-5 transition-colors hover:border-blue-500/50">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white">{{ $plan->name }}</h3>
                        <p class="mt-1 text-sm text-zinc-400">{{ $plan->validityLabel }}</p>

                        @if($plan->speed_down || $plan->speed_up)
                            <p class="mt-1 text-xs text-zinc-500">
                                {{ $plan->speed_down ? '↓ '.$plan->speed_down.'Mbps' : '' }}
                                {{ $plan->speed_up ? '↑ '.$plan->speed_up.'Mbps' : '' }}
                            </p>
                        @endif
                    </div>

                    <div class="mt-4 flex items-end justify-between">
                        <div>
                            <span class="text-2xl font-bold text-white">₵{{ $plan->formattedPrice }}</span>
                        </div>
                        <a
                            href="{{ route('portal.checkout', $plan) }}"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 transition-colors"
                        >Buy Now</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
