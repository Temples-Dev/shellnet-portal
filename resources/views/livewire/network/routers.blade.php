<flux:main>
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">Routers</flux:heading>
            <flux:subheading>{{ $this->routers->total() }} routers registered</flux:subheading>
        </div>
        <flux:button wire:click="openCreate" icon="plus" variant="primary">Add Router</flux:button>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search name or IP…" icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="statusFilter" class="w-40">
            <flux:select.option value="">All Statuses</flux:select.option>
            <flux:select.option value="Online">Online</flux:select.option>
            <flux:select.option value="Offline">Offline</flux:select.option>
        </flux:select>
    </div>

    {{-- Table --}}
    <flux:table :paginate="$this->routers">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>IP Address</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Last Seen</flux:table.column>
            <flux:table.column>Enabled</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($this->routers as $router)
                <flux:table.row :key="$router->id" wire:key="router-{{ $router->id }}">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <span class="inline-block size-2 rounded-full {{ $router->status === 'Online' ? 'bg-green-500' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $router->name }}</span>
                        </div>
                        @if($router->description)
                            <p class="text-xs text-zinc-400 mt-0.5">{{ $router->description }}</p>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-mono text-sm text-zinc-500">
                        {{ $router->ip_address }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if(isset($pingResults[$router->id]))
                            <flux:badge size="sm" :color="$pingResults[$router->id] === 'Online' ? 'green' : 'red'">
                                {{ $pingResults[$router->id] }}
                            </flux:badge>
                        @else
                            <flux:badge size="sm" :color="$router->status === 'Online' ? 'green' : 'zinc'">
                                {{ $router->status }}
                            </flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="text-sm text-zinc-400">
                        {{ $router->last_seen?->diffForHumans() ?? '—' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <button wire:click="toggleEnabled({{ $router->id }})">
                            <flux:badge size="sm" :color="$router->enabled ? 'green' : 'zinc'">
                                {{ $router->enabled ? 'Yes' : 'No' }}
                            </flux:badge>
                        </button>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            <flux:button
                                wire:click="ping({{ $router->id }})"
                                wire:loading.attr="disabled"
                                wire:target="ping({{ $router->id }})"
                                variant="ghost"
                                size="sm"
                                icon="signal"
                                inset="top bottom"
                                title="Ping router"
                            />
                            <flux:dropdown align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="openEdit({{ $router->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $router->id }})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-12 text-center text-zinc-400">No routers found</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Create / Edit Modal --}}
    <flux:modal name="router-form" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">{{ $editingId ? 'Edit Router' : 'Add Router' }}</flux:heading>
        <flux:subheading class="mb-6">{{ $editingId ? 'Update router settings' : 'Register a new RouterOS device' }}</flux:subheading>

        <form wire:submit.prevent="save" class="space-y-4">
            <flux:field>
                <flux:label>Router Name <flux:error name="fName" /></flux:label>
                <flux:input wire:model="fName" placeholder="e.g. Main Tower" />
            </flux:field>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>IP Address <flux:error name="fIpAddress" /></flux:label>
                    <flux:input wire:model="fIpAddress" placeholder="192.168.1.1:8728" />
                </flux:field>
                <flux:field>
                    <flux:label>Username <flux:error name="fUsername" /></flux:label>
                    <flux:input wire:model="fUsername" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>
                    Password
                    {{ $editingId ? '(leave blank to keep current)' : '' }}
                    <flux:error name="fPassword" />
                </flux:label>
                <flux:input wire:model="fPassword" type="password" placeholder="RouterOS API password" />
            </flux:field>
            <flux:field>
                <flux:label>Description</flux:label>
                <flux:input wire:model="fDescription" placeholder="Optional notes" />
            </flux:field>
            <flux:checkbox wire:model="fEnabled" label="Enabled" />
            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ $editingId ? 'Save Changes' : 'Add Router' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modal --}}
    <flux:modal name="delete-router" class="max-w-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-red-100 p-2 dark:bg-red-900/30">
                    <flux:icon.trash class="size-5 text-red-600 dark:text-red-400" />
                </span>
                <flux:heading size="lg">Delete Router</flux:heading>
            </div>
            <flux:text>Delete <strong>{{ $deletingName }}</strong>? Plans and subscriptions linked to this router will lose their router reference.</flux:text>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>
</flux:main>
