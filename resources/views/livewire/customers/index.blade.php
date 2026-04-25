<flux:main>
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Customers</flux:heading>
            <flux:subheading>{{ $this->customers->total() }} total customers</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Customer</flux:button>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search name, username, email, phone…" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:option value="">All Statuses</flux:option>
            @foreach(\App\Models\Customer::STATUSES as $s)
                <flux:option :value="$s">{{ $s }}</flux:option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="serviceFilter" class="w-40">
            <flux:option value="">All Services</flux:option>
            @foreach(\App\Models\Customer::SERVICE_TYPES as $t)
                <flux:option :value="$t">{{ $t }}</flux:option>
            @endforeach
        </flux:select>
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->customers">
        <flux:table.columns>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'fullname'"
                :direction="$sortDirection"
                wire:click="sort('fullname')"
            >Name</flux:table.column>
            <flux:table.column>Username</flux:table.column>
            <flux:table.column>Service</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'status'"
                :direction="$sortDirection"
                wire:click="sort('status')"
            >Status</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'created_at'"
                :direction="$sortDirection"
                wire:click="sort('created_at')"
            >Joined</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->customers as $customer)
                <flux:table.row :key="$customer->id" wire:key="customer-{{ $customer->id }}">
                    <flux:table.cell class="flex items-center gap-3">
                        <flux:avatar
                            size="sm"
                            :name="$customer->fullname"
                            class="shrink-0"
                        />
                        <div>
                            <a href="{{ route('customers.show', $customer->id) }}" wire:navigate
                               class="font-medium text-zinc-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400">
                                {{ $customer->fullname }}
                            </a>
                            @if($customer->email)
                                <p class="text-xs text-zinc-400">{{ $customer->email }}</p>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="font-mono text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $customer->username }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($customer->service_type) {
                            'Hotspot' => 'blue',
                            'PPPoE'   => 'purple',
                            'VPN'     => 'cyan',
                            default   => 'zinc',
                        }">{{ $customer->service_type }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($customer->status) {
                            'Active'    => 'green',
                            'Inactive'  => 'zinc',
                            'Banned'    => 'red',
                            'Disabled'  => 'orange',
                            'Limited'   => 'yellow',
                            'Suspended' => 'amber',
                            default     => 'zinc',
                        }">{{ $customer->status }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-400 whitespace-nowrap">
                        {{ $customer->created_at->format('d M Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item
                                    icon="eye"
                                    :href="route('customers.show', $customer->id)"
                                    wire:navigate
                                >View</flux:menu.item>
                                <flux:menu.item
                                    icon="pencil"
                                    wire:click="openEdit({{ $customer->id }})"
                                >Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item
                                    icon="trash"
                                    variant="danger"
                                    wire:click="confirmDelete({{ $customer->id }})"
                                >Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">
                        No customers found
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create Modal --}}
    <flux:modal name="create-customer" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">Add Customer</flux:heading>
        <flux:subheading class="mb-6">Create a new customer account</flux:subheading>

        <form wire:submit.prevent="saveCreate" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Full Name <flux:error name="createFullname" /></flux:label>
                    <flux:input wire:model="createFullname" placeholder="John Doe" />
                </flux:field>
                <flux:field>
                    <flux:label>Username <flux:error name="createUsername" /></flux:label>
                    <flux:input wire:model="createUsername" placeholder="john.doe" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Email <flux:error name="createEmail" /></flux:label>
                    <flux:input wire:model="createEmail" type="email" placeholder="john@example.com" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone <flux:error name="createPhone" /></flux:label>
                    <flux:input wire:model="createPhone" placeholder="+254 7xx xxx xxx" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Password <flux:error name="createPassword" /></flux:label>
                <flux:input wire:model="createPassword" type="password" placeholder="Min 6 characters" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Service Type</flux:label>
                    <flux:select wire:model="createServiceType">
                        @foreach(\App\Models\Customer::SERVICE_TYPES as $t)
                            <flux:option :value="$t">{{ $t }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="createStatus">
                        @foreach(\App\Models\Customer::STATUSES as $s)
                            <flux:option :value="$s">{{ $s }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Create Customer</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal name="edit-customer" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">Edit Customer</flux:heading>
        <flux:subheading class="mb-6">Update customer information</flux:subheading>

        <form wire:submit.prevent="saveEdit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Full Name <flux:error name="editFullname" /></flux:label>
                    <flux:input wire:model="editFullname" />
                </flux:field>
                <flux:field>
                    <flux:label>Username <flux:error name="editUsername" /></flux:label>
                    <flux:input wire:model="editUsername" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Email <flux:error name="editEmail" /></flux:label>
                    <flux:input wire:model="editEmail" type="email" />
                </flux:field>
                <flux:field>
                    <flux:label>Phone <flux:error name="editPhone" /></flux:label>
                    <flux:input wire:model="editPhone" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Service Type</flux:label>
                    <flux:select wire:model="editServiceType">
                        @foreach(\App\Models\Customer::SERVICE_TYPES as $t)
                            <flux:option :value="$t">{{ $t }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="editStatus">
                        @foreach(\App\Models\Customer::STATUSES as $s)
                            <flux:option :value="$s">{{ $s }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirm Modal --}}
    <flux:modal name="delete-customer" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete Customer</flux:heading>
            </div>
            <flux:text>
                Are you sure you want to delete <strong>{{ $deletingName }}</strong>?
                This will also remove their subscriptions and transactions.
            </flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
