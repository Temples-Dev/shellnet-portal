<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Optical Distribution Points</flux:heading>
            <flux:subheading>{{ $this->odps->total() }} ODPs configured</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add ODP</flux:button>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or address…" icon="magnifying-glass" class="max-w-sm" />
    </div>

    <flux:table :paginate="$this->odps">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Ports</flux:table.column>
            <flux:table.column>Attenuation</flux:table.column>
            <flux:table.column>Coverage (m)</flux:table.column>
            <flux:table.column>Coordinates</flux:table.column>
            <flux:table.column>Address</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->odps as $odp)
                <flux:table.row :key="$odp->id" wire:key="odp-{{ $odp->id }}">
                    <flux:table.cell variant="strong">{{ $odp->name }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-600 dark:text-zinc-300">{{ $odp->port_amount }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-sm text-zinc-500">{{ $odp->attenuation !== null ? $odp->attenuation . ' dB' : '—' }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $odp->coverage ? number_format($odp->coverage) : '—' }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-xs text-zinc-400">{{ $odp->coordinates ?? '—' }}</flux:table.cell>
                    <flux:table.cell class="max-w-xs truncate text-sm text-zinc-500">{{ $odp->address ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $odp->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $odp->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="py-12 text-center text-zinc-400">No ODPs found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="odp-form" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit ODP' : 'Add ODP' }}</flux:heading>
        <flux:subheading class="mb-6">Optical distribution point details</flux:subheading>
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Name <flux:error name="fName" /></flux:label>
                    <flux:input wire:model="fName" placeholder="ODP-001" />
                </flux:field>
                <flux:field>
                    <flux:label>Port Count <flux:error name="fPortAmount" /></flux:label>
                    <flux:input wire:model="fPortAmount" type="number" min="0" placeholder="8" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Attenuation (dB) <flux:error name="fAttenuation" /></flux:label>
                    <flux:input wire:model="fAttenuation" type="number" step="0.01" placeholder="3.5" />
                </flux:field>
                <flux:field>
                    <flux:label>Coverage Radius (m) <flux:error name="fCoverage" /></flux:label>
                    <flux:input wire:model="fCoverage" type="number" min="0" placeholder="500" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Coordinates (lat,lng) <flux:error name="fCoordinates" /></flux:label>
                <flux:input wire:model="fCoordinates" placeholder="6.5244,3.3792" />
            </flux:field>
            <flux:field>
                <flux:label>Address <flux:error name="fAddress" /></flux:label>
                <flux:textarea wire:model="fAddress" rows="2" placeholder="12 Lagos St, Victoria Island" />
            </flux:field>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="odp-delete" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete ODP</flux:heading>
            </div>
            <flux:text>Delete <strong>{{ $deletingName }}</strong>? This cannot be undone.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
