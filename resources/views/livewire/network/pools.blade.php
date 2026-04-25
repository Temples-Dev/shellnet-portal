<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">IP Pools</flux:heading>
            <flux:subheading>{{ $this->pools->total() }} pools configured</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Pool</flux:button>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search pools…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="routerFilter" class="w-48">
            <flux:option value="">All Routers</flux:option>
            @foreach($this->routers as $r)
                <flux:option :value="$r->id">{{ $r->name }}</flux:option>
            @endforeach
        </flux:select>
    </div>

    <flux:table :paginate="$this->pools">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Local IP</flux:table.column>
            <flux:table.column>Range</flux:table.column>
            <flux:table.column>Router</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->pools as $pool)
                <flux:table.row :key="$pool->id" wire:key="pool-{{ $pool->id }}">
                    <flux:table.cell variant="strong">{{ $pool->name }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-sm">{{ $pool->local_ip }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-sm text-zinc-500">{{ $pool->range_ip }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $pool->router?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $pool->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $pool->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-12 text-center text-zinc-400">No IP pools found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="pool-form" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit Pool' : 'Add IP Pool' }}</flux:heading>
        <flux:subheading class="mb-6">Configure address pool for router</flux:subheading>
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:field>
                <flux:label>Pool Name <flux:error name="fName" /></flux:label>
                <flux:input wire:model="fName" placeholder="e.g. hotspot-pool" />
            </flux:field>
            <flux:field>
                <flux:label>Local IP <flux:error name="fLocalIp" /></flux:label>
                <flux:input wire:model="fLocalIp" placeholder="192.168.88.1" />
            </flux:field>
            <flux:field>
                <flux:label>IP Range <flux:error name="fRangeIp" /></flux:label>
                <flux:input wire:model="fRangeIp" placeholder="192.168.88.2-192.168.88.254" />
            </flux:field>
            <flux:field>
                <flux:label>Router</flux:label>
                <flux:select wire:model="fRouterId">
                    <flux:option value="">— None —</flux:option>
                    @foreach($this->routers as $r)
                        <flux:option :value="$r->id">{{ $r->name }}</flux:option>
                    @endforeach
                </flux:select>
            </flux:field>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="pool-delete" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete Pool</flux:heading>
            </div>
            <flux:text>Delete pool <strong>{{ $deletingName }}</strong>?</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
