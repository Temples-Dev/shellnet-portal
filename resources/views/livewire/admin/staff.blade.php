<flux:main>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Staff</flux:heading>
            <flux:subheading>{{ $this->staff->total() }} team members</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Staff</flux:button>
    </div>

    <div class="mb-4 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search name or email…" icon="magnifying-glass" class="flex-1 min-w-48" />
        <flux:select wire:model.live="roleFilter" class="w-40">
            <flux:option value="">All Roles</flux:option>
            @foreach(\App\Models\User::USER_TYPES as $r)
                <flux:option :value="$r">{{ $r }}</flux:option>
            @endforeach
        </flux:select>
    </div>

    <flux:table :paginate="$this->staff">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Role</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Joined</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->staff as $user)
                <flux:table.row :key="$user->id" wire:key="staff-{{ $user->id }}">
                    <flux:table.cell class="flex items-center gap-3">
                        <flux:avatar size="sm" :name="$user->name" class="shrink-0" />
                        <div>
                            <p class="font-medium text-zinc-900 dark:text-white">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <flux:badge size="sm" color="blue" class="ml-1">You</flux:badge>
                                @endif
                            </p>
                            <p class="text-xs text-zinc-400">{{ $user->email }}</p>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="match($user->user_type) {
                            'SuperAdmin' => 'red',
                            'Admin'      => 'orange',
                            'Report'     => 'blue',
                            'Agent'      => 'purple',
                            'Sales'      => 'green',
                            default      => 'zinc',
                        }">{{ $user->user_type ?? 'Agent' }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="($user->status ?? 'Active') === 'Active' ? 'green' : 'zinc'">
                            {{ $user->status ?? 'Active' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-400 whitespace-nowrap">
                        {{ $user->created_at->format('d M Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="openEdit({{ $user->id }})">Edit</flux:menu.item>
                                @if($user->id !== auth()->id())
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $user->id }})">Delete</flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-12 text-center text-zinc-400">No staff found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="staff-form" class="w-full max-w-md">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit Staff Member' : 'Add Staff Member' }}</flux:heading>
        <flux:subheading class="mb-6">{{ $editingId ? 'Update details and role' : 'Create a new portal account' }}</flux:subheading>
        <form wire:submit.prevent="save" class="space-y-4">
            <flux:field>
                <flux:label>Full Name <flux:error name="fName" /></flux:label>
                <flux:input wire:model="fName" placeholder="Jane Doe" />
            </flux:field>
            <flux:field>
                <flux:label>Email <flux:error name="fEmail" /></flux:label>
                <flux:input wire:model="fEmail" type="email" placeholder="jane@company.com" />
            </flux:field>
            <flux:field>
                <flux:label>
                    Password {{ $editingId ? '(leave blank to keep current)' : '' }}
                    <flux:error name="fPassword" />
                </flux:label>
                <flux:input wire:model="fPassword" type="password" placeholder="Min 8 characters" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Role</flux:label>
                    <flux:select wire:model="fUserType">
                        @foreach(\App\Models\User::USER_TYPES as $r)
                            <flux:option :value="$r">{{ $r }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Status</flux:label>
                    <flux:select wire:model="fStatus">
                        @foreach(\App\Models\User::STATUSES as $s)
                            <flux:option :value="$s">{{ $s }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save Changes' : 'Create Account' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="staff-delete" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Remove Staff Member</flux:heading>
            </div>
            <flux:text>Remove <strong>{{ $deletingName }}</strong> from the portal? This cannot be undone.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Remove</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
