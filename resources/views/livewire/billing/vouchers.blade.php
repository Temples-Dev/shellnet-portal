<flux:main>
    @if(session('routerWarning'))
        <div class="mb-4 rounded-lg border border-yellow-500/20 bg-yellow-950/30 px-4 py-3 text-sm text-yellow-400">
            {{ session('routerWarning') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Vouchers</flux:heading>
            <flux:subheading>{{ number_format($this->vouchers->total()) }} voucher codes</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="exportCsv" icon="arrow-down-tray" variant="outline">Export</flux:button>
            <flux:button wire:click="openGenerate" icon="plus" variant="primary">Generate</flux:button>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search code…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="statusFilter" class="w-36">
            <flux:select.option value="">All Status</flux:select.option>
            @foreach(\App\Models\Voucher::STATUSES as $s)
                <flux:select.option :value="$s">{{ ucfirst($s) }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="planFilter" class="w-48">
            <flux:select.option value="">All Plans</flux:select.option>
            @foreach($this->plans as $p)
                <flux:select.option :value="$p->id">{{ $p->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <flux:table :paginate="$this->vouchers">
        <flux:table.columns>
            <flux:table.column>Code</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Plan</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Used At</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->vouchers as $v)
                <flux:table.row :key="$v->id" wire:key="v-{{ $v->id }}">
                    <flux:table.cell class="font-mono font-medium tracking-wider text-zinc-700 dark:text-zinc-200">{{ $v->code }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="blue">{{ $v->type }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $v->plan?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($v->status) { 'unused' => 'green', 'used' => 'zinc', 'disabled' => 'red', default => 'zinc' }">{{ ucfirst($v->status) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-400 whitespace-nowrap">
                        {{ $v->used_at?->format('d M Y H:i') ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($v->status === 'unused')
                            <flux:dropdown align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item icon="no-symbol" wire:click="disable({{ $v->id }})">Disable</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $v->id }})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">No vouchers found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="voucher-generate" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-1">Generate Vouchers</flux:heading>
        <flux:subheading class="mb-6">Bulk-create redeemable voucher codes</flux:subheading>
        <form wire:submit.prevent="generate" class="space-y-4">
            <flux:field>
                <flux:label>Quantity <flux:error name="genCount" /></flux:label>
                <flux:input wire:model="genCount" type="number" min="1" max="500" placeholder="10" />
            </flux:field>
            <flux:field>
                <flux:label>Plan <flux:error name="genPlanId" /></flux:label>
                <flux:select wire:model="genPlanId">
                    <flux:select.option value="">Select plan…</flux:select.option>
                    @foreach($this->plans as $p)
                        <flux:select.option :value="$p->id">{{ $p->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model="genType">
                        <flux:select.option value="Hotspot">Hotspot</flux:select.option>
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Router (optional)</flux:label>
                    <flux:select wire:model="genRouterId">
                        <flux:select.option value="">Any</flux:select.option>
                        @foreach($this->routers as $r)
                            <flux:select.option :value="$r->id">{{ $r->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="ticket">Generate</flux:button>
            </div>
        </form>
    </flux:modal>
</flux:main>
