<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Bandwidth Profiles</flux:heading>
            <flux:subheading>{{ $this->bandwidths->total() }} profiles</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Profile</flux:button>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search profiles…" icon="magnifying-glass" class="max-w-xs" />
    </div>

    <flux:table :paginate="$this->bandwidths">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Download</flux:table.column>
            <flux:table.column>Upload</flux:table.column>
            <flux:table.column>RouterOS Rate Limit</flux:table.column>
            <flux:table.column>Plans</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->bandwidths as $bw)
                <flux:table.row :key="$bw->id" wire:key="bw-{{ $bw->id }}">
                    <flux:table.cell variant="strong">{{ $bw->name }}</flux:table.cell>
                    <flux:table.cell>{{ $bw->rate_down }} {{ $bw->rate_down_unit }}</flux:table.cell>
                    <flux:table.cell>{{ $bw->rate_up }} {{ $bw->rate_up_unit }}</flux:table.cell>
                    <flux:table.cell class="font-mono text-sm text-zinc-500">{{ $bw->rate_limit_string }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-400">{{ $bw->plans_count ?? $bw->plans()->count() }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $bw->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $bw->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">No bandwidth profiles found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="bw-form" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit Profile' : 'Add Bandwidth Profile' }}</flux:heading>
        <flux:subheading class="mb-6">Set download and upload rates</flux:subheading>
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:field>
                <flux:label>Profile Name <flux:error name="fName" /></flux:label>
                <flux:input wire:model="fName" placeholder="e.g. 10Mbps/5Mbps" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Download Rate <flux:error name="fRateDown" /></flux:label>
                    <flux:input wire:model="fRateDown" type="number" min="1" />
                </flux:field>
                <flux:field>
                    <flux:label>Unit</flux:label>
                    <flux:select wire:model="fRateDownUnit">
                        @foreach($this->units() as $u)
                            <flux:select.option :value="$u">{{ $u }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Upload Rate <flux:error name="fRateUp" /></flux:label>
                    <flux:input wire:model="fRateUp" type="number" min="1" />
                </flux:field>
                <flux:field>
                    <flux:label>Unit</flux:label>
                    <flux:select wire:model="fRateUpUnit">
                        @foreach($this->units() as $u)
                            <flux:select.option :value="$u">{{ $u }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Burst (optional)</flux:label>
                <flux:input wire:model="fBurst" placeholder="e.g. 20M/10M 0/0 0/0 8 1/1" />
            </flux:field>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="bw-delete" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete Profile</flux:heading>
            </div>
            <flux:text>Delete <strong>{{ $deletingName }}</strong>? Plans using this profile will lose their bandwidth reference.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
