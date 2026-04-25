<flux:main>
    {{-- Back + actions --}}
    <div class="mb-6 flex items-center justify-between">
        <flux:button
            :href="route('customers.index')"
            wire:navigate
            variant="ghost"
            icon="arrow-left"
            size="sm"
        >All Customers</flux:button>
        <flux:button wire:click="openEdit" icon="pencil" size="sm">Edit Customer</flux:button>
    </div>

    {{-- Profile card --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col items-center text-center">
                <flux:avatar size="xl" :name="$this->customer->fullname" class="mb-3" />
                <flux:heading size="lg">{{ $this->customer->fullname }}</flux:heading>
                <flux:text class="font-mono text-sm text-zinc-400">{{ $this->customer->username }}</flux:text>
                <div class="mt-2 flex gap-2">
                    <flux:badge :color="match($this->customer->status) {
                        'Active'    => 'green',
                        'Inactive'  => 'zinc',
                        'Banned'    => 'red',
                        'Disabled'  => 'orange',
                        'Limited'   => 'yellow',
                        'Suspended' => 'amber',
                        default     => 'zinc',
                    }">{{ $this->customer->status }}</flux:badge>
                    <flux:badge color="blue" size="sm">{{ $this->customer->service_type }}</flux:badge>
                </div>
            </div>

            <div class="mt-6 space-y-3 border-t border-zinc-100 pt-4 dark:border-zinc-700/60">
                @if($this->customer->email)
                    <div class="flex items-center gap-2 text-sm">
                        <flux:icon.envelope class="size-4 shrink-0 text-zinc-400" />
                        <span class="text-zinc-600 dark:text-zinc-300 truncate">{{ $this->customer->email }}</span>
                    </div>
                @endif
                @if($this->customer->phone)
                    <div class="flex items-center gap-2 text-sm">
                        <flux:icon.phone class="size-4 shrink-0 text-zinc-400" />
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $this->customer->phone }}</span>
                    </div>
                @endif
                @if($this->customer->city)
                    <div class="flex items-center gap-2 text-sm">
                        <flux:icon.map-pin class="size-4 shrink-0 text-zinc-400" />
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $this->customer->city }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-2 text-sm">
                    <flux:icon.calendar class="size-4 shrink-0 text-zinc-400" />
                    <span class="text-zinc-600 dark:text-zinc-300">Joined {{ $this->customer->created_at->format('d M Y') }}</span>
                </div>
            </div>

            <div class="mt-4 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                <flux:text class="text-xs text-zinc-400">Account Balance</flux:text>
                <p class="text-xl font-bold text-zinc-900 dark:text-white">${{ number_format($this->customer->balance, 2) }}</p>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-4 lg:col-span-2">
            {{-- Active Subscription --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-4">Active Subscription</flux:heading>
                @if($this->activeSubscription)
                    @php $sub = $this->activeSubscription; @endphp
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <flux:text class="text-xs text-zinc-400">Plan</flux:text>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $sub->plan_name }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs text-zinc-400">Router</flux:text>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $sub->router_name }}</p>
                        </div>
                        <div>
                            <flux:text class="text-xs text-zinc-400">Expires</flux:text>
                            <p class="font-semibold text-zinc-900 dark:text-white">
                                {{ $sub->expires_at?->format('d M Y') ?? '—' }}
                            </p>
                        </div>
                        <div>
                            <flux:text class="text-xs text-zinc-400">Days Left</flux:text>
                            @php $days = $sub->days_remaining; @endphp
                            <p class="font-semibold {{ $days <= 3 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                                {{ $days !== null ? $days . ' days' : '—' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="h-1.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                            @php
                                $total = $sub->recharged_at && $sub->expires_at
                                    ? $sub->recharged_at->diffInDays($sub->expires_at)
                                    : 1;
                                $elapsed = $sub->recharged_at
                                    ? $sub->recharged_at->diffInDays(now())
                                    : 0;
                                $pct = $total > 0 ? min(100, round(($elapsed / $total) * 100)) : 100;
                            @endphp
                            <div
                                class="h-1.5 rounded-full {{ $pct >= 80 ? 'bg-red-500' : 'bg-blue-500' }}"
                                style="width: {{ $pct }}%"
                            ></div>
                        </div>
                        <flux:text class="mt-1 text-xs text-zinc-400">{{ $pct }}% elapsed</flux:text>
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-zinc-200 p-6 text-center dark:border-zinc-700">
                        <flux:icon.wifi-off class="mx-auto mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
                        <flux:text class="text-zinc-400">No active subscription</flux:text>
                    </div>
                @endif
            </div>

            {{-- Recent Transactions --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-700/60">
                    <flux:heading size="sm">Transaction History</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-100 dark:border-zinc-700/60">
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Invoice</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Plan</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Amount</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Method</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                            @forelse($this->transactions as $tx)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-5 py-3 font-mono text-xs text-zinc-400">{{ $tx->invoice }}</td>
                                    <td class="px-5 py-3 text-zinc-700 dark:text-zinc-300">{{ $tx->plan_name }}</td>
                                    <td class="px-5 py-3 font-semibold text-zinc-900 dark:text-white">${{ number_format($tx->price, 2) }}</td>
                                    <td class="px-5 py-3">
                                        <flux:badge size="sm" :color="match($tx->method) {
                                            'mpesa'   => 'green',
                                            'card'    => 'blue',
                                            'cash'    => 'zinc',
                                            'voucher' => 'yellow',
                                            default   => 'zinc',
                                        }">{{ ucfirst($tx->method ?? '—') }}</flux:badge>
                                    </td>
                                    <td class="px-5 py-3 text-zinc-400 whitespace-nowrap">{{ $tx->created_at?->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-zinc-400">No transactions</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <flux:modal name="edit-customer" class="w-full max-w-lg">
        <flux:heading size="lg" class="mb-1">Edit Customer</flux:heading>
        <flux:subheading class="mb-6">{{ $this->customer->fullname }}</flux:subheading>

        <form wire:submit.prevent="saveEdit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Full Name <flux:error name="editFullname" /></flux:label>
                    <flux:input wire:model="editFullname" />
                </flux:field>
                <flux:field>
                    <flux:label>Email <flux:error name="editEmail" /></flux:label>
                    <flux:input wire:model="editEmail" type="email" />
                </flux:field>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Phone <flux:error name="editPhone" /></flux:label>
                    <flux:input wire:model="editPhone" />
                </flux:field>
                <flux:field>
                    <flux:label>City <flux:error name="editCity" /></flux:label>
                    <flux:input wire:model="editCity" />
                </flux:field>
            </div>
            <flux:field>
                <flux:label>Address</flux:label>
                <flux:input wire:model="editAddress" />
            </flux:field>
            <div class="grid grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>Service</flux:label>
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
                <flux:field>
                    <flux:label>Account</flux:label>
                    <flux:select wire:model="editAccountType">
                        @foreach(\App\Models\Customer::ACCOUNT_TYPES as $a)
                            <flux:option :value="$a">{{ $a }}</flux:option>
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
</flux:main>
