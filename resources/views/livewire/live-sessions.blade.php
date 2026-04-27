<div wire:poll.30s class="mt-6 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-700/60">
        <div class="flex items-center gap-2">
            <flux:heading size="sm">Live Sessions</flux:heading>
            @if(count($this->sessions) > 0)
                <flux:badge color="green" size="sm">{{ count($this->sessions) }} online</flux:badge>
            @else
                <flux:badge color="zinc" size="sm">0 online</flux:badge>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-zinc-400">auto-refreshes every 30s</span>
            <button
                wire:click="$refresh"
                class="rounded-md p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 transition-colors"
                title="Refresh now"
            >
                <svg wire:loading.class="animate-spin" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-700/60">
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">User</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">IP Address</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">MAC</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Uptime</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Down</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Up</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Router</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                @forelse($this->sessions as $session)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $session['user'] }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $session['address'] }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $session['mac'] }}</td>
                        <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">{{ $session['uptime'] }}</td>
                        <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400 tabular-nums">{{ $session['bytes_in'] }}</td>
                        <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400 tabular-nums">{{ $session['bytes_out'] }}</td>
                        <td class="px-5 py-3">
                            <flux:badge size="sm" color="blue">{{ $session['router'] }}</flux:badge>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-zinc-400">
                            No active hotspot sessions
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
