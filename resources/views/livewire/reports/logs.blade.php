<flux:main>
    <div class="mb-6">
        <flux:heading size="xl">Activity Logs</flux:heading>
        <flux:subheading>Audit trail of admin actions</flux:subheading>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search descriptions…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="typeFilter" class="w-52">
            <flux:option value="">All Event Types</flux:option>
            @foreach($this->types as $type)
                <flux:option :value="$type">{{ str_replace('_', ' ', $type) }}</flux:option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live="dateFrom" type="date" class="w-36" />
        <flux:input wire:model.live="dateTo" type="date" class="w-36" />
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700/60">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Event</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Description</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">IP</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                    @forelse($this->logs as $log)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40" wire:key="log-{{ $log->id }}">
                            <td class="px-5 py-3">
                                <flux:badge size="sm" color="zinc">
                                    {{ str_replace('_', ' ', $log->type) }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-zinc-700 dark:text-zinc-300 max-w-md truncate">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-zinc-400">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-zinc-400 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-zinc-400">No log entries found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-700/60">
            {{ $this->logs->links() }}
        </div>
    </div>
</flux:main>
