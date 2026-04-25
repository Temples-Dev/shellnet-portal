<flux:main>
    <div class="mb-6">
        <flux:heading size="xl">Export Data</flux:heading>
        <flux:subheading>Download records as CSV</flux:subheading>
    </div>

    <div class="max-w-xl">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form wire:submit.prevent="export" class="space-y-5">
                <flux:field>
                    <flux:label>Dataset <flux:error name="entity" /></flux:label>
                    <flux:select wire:model.live="entity">
                        @foreach(\App\Livewire\Reports\Export::ENTITIES as $key => $label)
                            <flux:option :value="$key">{{ $label }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>From <flux:error name="dateFrom" /></flux:label>
                        <flux:input wire:model="dateFrom" type="date" />
                    </flux:field>
                    <flux:field>
                        <flux:label>To <flux:error name="dateTo" /></flux:label>
                        <flux:input wire:model="dateTo" type="date" />
                    </flux:field>
                </div>

                @if($entity === 'customers')
                <flux:field>
                    <flux:label>Status Filter</flux:label>
                    <flux:select wire:model="statusFilter">
                        <flux:option value="">All Statuses</flux:option>
                        @foreach(\App\Models\Customer::STATUSES as $s)
                            <flux:option :value="$s">{{ $s }}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:field>
                @endif

                <div class="rounded-lg bg-zinc-50 p-4 text-sm text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <p class="font-medium text-zinc-700 dark:text-zinc-200">Included columns:</p>
                    @if($entity === 'transactions')
                        <p class="mt-1">Invoice · Customer · Username · Plan · Amount · Method · Date</p>
                    @elseif($entity === 'customers')
                        <p class="mt-1">ID · Full Name · Username · Email · Phone · Service · Status · Created</p>
                    @else
                        <p class="mt-1">ID · Customer · Username · Plan · Status · Recharged At · Expires At</p>
                    @endif
                </div>

                <flux:button type="submit" variant="primary" icon="arrow-down-tray" class="w-full">
                    Download CSV
                </flux:button>
            </form>
        </div>
    </div>
</flux:main>
