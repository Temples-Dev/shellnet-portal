<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Transactions</flux:heading>
            <flux:subheading>
                {{ number_format($this->totals['count']) }} records · ${{ number_format($this->totals['revenue'], 2) }} total
            </flux:subheading>
        </div>
        <flux:button wire:click="exportCsv" icon="arrow-down-tray" variant="outline">Export CSV</flux:button>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Invoice, username, plan…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="typeFilter" class="w-36">
            <flux:option value="">All Types</flux:option>
            @foreach(\App\Models\Plan::TYPES as $t)
                <flux:option :value="$t">{{ $t }}</flux:option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="methodFilter" class="w-36">
            <flux:option value="">All Methods</flux:option>
            @foreach(['cash','mpesa','card','paystack','voucher'] as $m)
                <flux:option :value="$m">{{ ucfirst($m) }}</flux:option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live="dateFrom" type="date" class="w-36" />
        <flux:input wire:model.live="dateTo" type="date" class="w-36" />
    </div>

    <flux:table :paginate="$this->transactions">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'invoice'" :direction="$sortDirection" wire:click="sort('invoice')">Invoice</flux:table.column>
            <flux:table.column>Customer</flux:table.column>
            <flux:table.column>Plan</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'price'" :direction="$sortDirection" wire:click="sort('price')">Amount</flux:table.column>
            <flux:table.column>Method</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">Date</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->transactions as $tx)
                <flux:table.row :key="$tx->id" wire:key="tx-{{ $tx->id }}">
                    <flux:table.cell class="font-mono text-xs text-zinc-500">{{ $tx->invoice }}</flux:table.cell>
                    <flux:table.cell>
                        @if($tx->customer)
                            <a href="{{ route('customers.show', $tx->customer->id) }}" wire:navigate
                               class="font-medium text-zinc-900 hover:text-blue-600 dark:text-white">
                                {{ $tx->customer->fullname }}
                            </a>
                            <p class="text-xs text-zinc-400">{{ $tx->username }}</p>
                        @else
                            <span class="text-zinc-400">{{ $tx->username }}</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $tx->plan_name }}</flux:table.cell>
                    <flux:table.cell variant="strong">${{ number_format($tx->price, 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($tx->method) {
                            'mpesa','paystack' => 'green',
                            'card'    => 'blue',
                            'cash'    => 'zinc',
                            'voucher' => 'yellow',
                            default   => 'zinc',
                        }">{{ ucfirst($tx->method ?? '—') }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-400 whitespace-nowrap">
                        {{ $tx->created_at?->format('d M Y H:i') }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">No transactions found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:main>
