<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Coupons</flux:heading>
            <flux:subheading>{{ number_format($this->coupons->total()) }} discount codes</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Coupon</flux:button>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search code or description…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="statusFilter" class="w-36">
            <flux:select.option value="">All Status</flux:select.option>
            <flux:select.option value="active">Active</flux:select.option>
            <flux:select.option value="inactive">Inactive</flux:select.option>
        </flux:select>
    </div>

    <flux:table :paginate="$this->coupons">
        <flux:table.columns>
            <flux:table.column>Code</flux:table.column>
            <flux:table.column>Type / Value</flux:table.column>
            <flux:table.column>Usage</flux:table.column>
            <flux:table.column>Valid Until</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->coupons as $c)
                <flux:table.row :key="$c->id" wire:key="c-{{ $c->id }}">
                    <flux:table.cell>
                        <span class="font-mono font-semibold text-zinc-800 dark:text-zinc-100">{{ $c->code }}</span>
                        @if($c->description)
                            <p class="text-xs text-zinc-400">{{ $c->description }}</p>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($c->type === 'percent')
                            <flux:badge size="sm" color="blue">{{ $c->value }}%</flux:badge>
                        @else
                            <flux:badge size="sm" color="green">₵{{ number_format($c->value, 2) }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">
                        {{ number_format($c->usage_count) }}{{ $c->max_usage ? ' / ' . number_format($c->max_usage) : ' / ∞' }}
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-400 whitespace-nowrap">
                        {{ $c->expires_at?->format('d M Y') ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            :color="$c->status === 'active' ? 'green' : 'zinc'"
                            class="cursor-pointer"
                            wire:click="toggleStatus({{ $c->id }})"
                        >{{ ucfirst($c->status) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $c->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $c->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">No coupons found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="coupon-form" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit Coupon' : 'Add Coupon' }}</flux:heading>
        <flux:subheading class="mb-6">{{ $editingId ? 'Update discount code' : 'Create a new discount code' }}</flux:subheading>
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Code <flux:error name="fCode" /></flux:label>
                    <flux:input wire:model="fCode" placeholder="SUMMER20" class="uppercase" />
                </flux:field>
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="fStatus">
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="inactive">Inactive</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Type <flux:error name="fType" /></flux:label>
                    <flux:select wire:model="fType">
                        <flux:select.option value="fixed">Fixed ($)</flux:select.option>
                        <flux:select.option value="percent">Percent (%)</flux:select.option>
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Value <flux:error name="fValue" /></flux:label>
                    <flux:input wire:model="fValue" type="number" step="0.01" placeholder="10" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Description <flux:error name="fDescription" /></flux:label>
                <flux:input wire:model="fDescription" placeholder="Summer sale discount" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Max Usage (blank = unlimited) <flux:error name="fMaxUsage" /></flux:label>
                    <flux:input wire:model="fMaxUsage" type="number" min="1" placeholder="100" />
                </flux:field>
                <flux:field>
                    <flux:label>Min Order Amount <flux:error name="fMinOrder" /></flux:label>
                    <flux:input wire:model="fMinOrder" type="number" step="0.01" placeholder="0" />
                </flux:field>
            </div>
            @if($fType === 'percent')
            <flux:field>
                <flux:label>Max Discount Cap (blank = unlimited) <flux:error name="fMaxDiscount" /></flux:label>
                <flux:input wire:model="fMaxDiscount" type="number" step="0.01" placeholder="50.00" />
            </flux:field>
            @endif
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Starts At <flux:error name="fStartsAt" /></flux:label>
                    <flux:input wire:model="fStartsAt" type="date" />
                </flux:field>
                <flux:field>
                    <flux:label>Expires At <flux:error name="fExpiresAt" /></flux:label>
                    <flux:input wire:model="fExpiresAt" type="date" />
                </flux:field>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save Changes' : 'Create Coupon' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="coupon-delete" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete Coupon</flux:heading>
            </div>
            <flux:text>Delete coupon <strong>{{ $deletingCode }}</strong>? This cannot be undone.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
