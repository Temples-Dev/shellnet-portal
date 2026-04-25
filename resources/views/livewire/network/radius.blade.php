<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">RADIUS / NAS</flux:heading>
            <flux:subheading>{{ $this->nasDevices->total() }} NAS devices configured</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add NAS</flux:button>
    </div>

    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by hostname or short name…" icon="magnifying-glass" class="max-w-sm" />
    </div>

    <flux:table :paginate="$this->nasDevices">
        <flux:table.columns>
            <flux:table.column>Hostname / IP</flux:table.column>
            <flux:table.column>Short Name</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column>Ports</flux:table.column>
            <flux:table.column>Router</flux:table.column>
            <flux:table.column>Description</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->nasDevices as $nas)
                <flux:table.row :key="$nas->id" wire:key="nas-{{ $nas->id }}">
                    <flux:table.cell class="font-mono font-medium text-zinc-800 dark:text-zinc-200">{{ $nas->nasname }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $nas->shortname ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $nas->type }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $nas->ports ?? '—' }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $nas->router?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell class="max-w-xs truncate text-sm text-zinc-400">{{ $nas->description ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $nas->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $nas->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="py-12 text-center text-zinc-400">No NAS devices found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="nas-form" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit NAS' : 'Add NAS Device' }}</flux:heading>
        <flux:subheading class="mb-6">RADIUS NAS / network access server</flux:subheading>
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Hostname / IP <flux:error name="fNasname" /></flux:label>
                    <flux:input wire:model="fNasname" placeholder="192.168.1.1" />
                </flux:field>
                <flux:field>
                    <flux:label>Short Name <flux:error name="fShortname" /></flux:label>
                    <flux:input wire:model="fShortname" placeholder="main-router" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model="fType">
                        @foreach(\App\Livewire\Network\Radius::NAS_TYPES as $t)
                            <flux:option :value="$t">{{ ucfirst($t) }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Ports <flux:error name="fPorts" /></flux:label>
                    <flux:input wire:model="fPorts" type="number" min="0" placeholder="1812" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>
                    Shared Secret {{ $editingId ? '(leave blank to keep)' : '' }}
                    <flux:error name="fSecret" />
                </flux:label>
                <flux:input wire:model="fSecret" type="password" placeholder="••••••••" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Server <flux:error name="fServer" /></flux:label>
                    <flux:input wire:model="fServer" placeholder="radius.local" />
                </flux:field>
                <flux:field>
                    <flux:label>Community <flux:error name="fCommunity" /></flux:label>
                    <flux:input wire:model="fCommunity" placeholder="public" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Router (optional)</flux:label>
                <flux:select wire:model="fRouterId">
                    <flux:option value="">— None —</flux:option>
                    @foreach($this->routers as $r)
                        <flux:option :value="$r->id">{{ $r->name }}</flux:option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>Description <flux:error name="fDescription" /></flux:label>
                <flux:input wire:model="fDescription" placeholder="Main office router" />
            </flux:field>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="nas-delete" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete NAS Device</flux:heading>
            </div>
            <flux:text>Delete <strong>{{ $deletingName }}</strong> from RADIUS? This cannot be undone.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
