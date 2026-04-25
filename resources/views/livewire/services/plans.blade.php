<flux:main>
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Plans</flux:heading>
            <flux:subheading>{{ $this->plans->total() }} plans configured</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Plan</flux:button>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search plans…" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="typeFilter" class="w-40">
            <flux:select.option value="">All Types</flux:select.option>
            @foreach(\App\Models\Plan::TYPES as $t)
                <flux:select.option :value="$t">{{ $t }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->plans">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">Name</flux:table.column>
            <flux:table.column>Type</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'price'" :direction="$sortDirection" wire:click="sort('price')">Price</flux:table.column>
            <flux:table.column>Validity</flux:table.column>
            <flux:table.column>Bandwidth</flux:table.column>
            <flux:table.column>Router</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->plans as $plan)
                <flux:table.row :key="$plan->id" wire:key="plan-{{ $plan->id }}">
                    <flux:table.cell variant="strong">{{ $plan->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($plan->type) {
                            'Hotspot' => 'blue',
                            'PPPoE'   => 'purple',
                            'VPN'     => 'cyan',
                            'Balance' => 'green',
                            default   => 'zinc',
                        }">{{ $plan->type }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">₵{{ number_format($plan->price, 2) }}</flux:table.cell>
                    <flux:table.cell class="text-zinc-500">{{ $plan->validity }} {{ $plan->validity_unit }}</flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-500">
                        {{ $plan->bandwidth?->name ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-500">
                        {{ $plan->router?->name ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <button wire:click="toggleEnabled({{ $plan->id }})" class="focus:outline-none">
                            <flux:badge size="sm" :color="$plan->enabled ? 'green' : 'zinc'">
                                {{ $plan->enabled ? 'Active' : 'Disabled' }}
                            </flux:badge>
                        </button>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $plan->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $plan->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="py-12 text-center text-zinc-400">No plans found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit Modal (shared) --}}
    <flux:modal name="plan-form" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit Plan' : 'Add Plan' }}</flux:heading>
        <flux:subheading class="mb-6">{{ $editingId ? 'Update plan details' : 'Create a new internet plan' }}</flux:subheading>

        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field class="col-span-2">
                    <flux:label>Plan Name <flux:error name="fName" /></flux:label>
                    <flux:input wire:model="fName" placeholder="e.g. Monthly Home 10Mbps" />
                </flux:field>
                <flux:field>
                    <flux:label>Type</flux:label>
                    <flux:select wire:model="fType">
                        @foreach(\App\Models\Plan::TYPES as $t)
                            <flux:select.option :value="$t">{{ $t }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Price ($) <flux:error name="fPrice" /></flux:label>
                    <flux:input wire:model="fPrice" type="number" step="0.01" min="0" placeholder="0.00" />
                </flux:field>
                <flux:field>
                    <flux:label>Validity <flux:error name="fValidity" /></flux:label>
                    <flux:input wire:model="fValidity" type="number" min="1" />
                </flux:field>
                <flux:field>
                    <flux:label>Validity Unit</flux:label>
                    <flux:select wire:model="fValidityUnit">
                        @foreach(\App\Models\Plan::VALIDITY_UNITS as $u)
                            <flux:select.option :value="$u">{{ $u }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Bandwidth Profile</flux:label>
                    <flux:select wire:model="fBandwidthId">
                        <flux:select.option value="">— None —</flux:select.option>
                        @foreach($this->bandwidths as $bw)
                            <flux:select.option :value="$bw->id">{{ $bw->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Router</flux:label>
                    <flux:select wire:model="fRouterId">
                        <flux:select.option value="">— None —</flux:select.option>
                        @foreach($this->routers as $r)
                            <flux:select.option :value="$r->id">{{ $r->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <div class="flex gap-6 pt-1">
                <flux:checkbox wire:model="fEnabled" label="Enabled" />
                <flux:checkbox wire:model="fPrepaid" label="Prepaid" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save Changes' : 'Create Plan' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal name="delete-plan" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete Plan</flux:heading>
            </div>
            <flux:text>Delete <strong>{{ $deletingName }}</strong>? Existing subscriptions referencing this plan will remain but no new ones can be created.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
