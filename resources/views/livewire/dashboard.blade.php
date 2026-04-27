<flux:main>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Dashboard</flux:heading>
            <flux:subheading>Welcome back, {{ auth()->user()->name }}</flux:subheading>
        </div>
        <flux:badge color="green" size="sm" icon="signal">Live</flux:badge>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Active Subscriptions --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active Subscriptions</flux:text>
                <span class="rounded-lg bg-blue-50 p-2 dark:bg-blue-950">
                    <flux:icon.users class="size-4 text-blue-600 dark:text-blue-400" />
                </span>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ number_format($this->activeSubscriptions) }}
            </p>
            <flux:text class="mt-1 text-xs text-zinc-400">Currently active plans</flux:text>
        </div>

        {{-- Revenue Today --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Revenue Today</flux:text>
                <span class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-950">
                    <flux:icon.banknotes class="size-4 text-emerald-600 dark:text-emerald-400" />
                </span>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                ₵{{ number_format($this->revenueToday, 2) }}
            </p>
            <flux:text class="mt-1 text-xs text-zinc-400">Collected since midnight</flux:text>
        </div>

        {{-- Expiring Today --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Expiring Today</flux:text>
                <span class="rounded-lg bg-amber-50 p-2 dark:bg-amber-950">
                    <flux:icon.clock class="size-4 text-amber-600 dark:text-amber-400" />
                </span>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight {{ $this->expiringToday > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">
                {{ number_format($this->expiringToday) }}
            </p>
            <flux:text class="mt-1 text-xs text-zinc-400">Plans ending today</flux:text>
        </div>

        {{-- Offline Routers --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between">
                <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Offline Routers</flux:text>
                <span class="rounded-lg {{ $this->offlineRouters > 0 ? 'bg-red-50 dark:bg-red-950' : 'bg-zinc-100 dark:bg-zinc-800' }} p-2">
                    <flux:icon.wifi class="size-4 {{ $this->offlineRouters > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-400' }}" />
                </span>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight {{ $this->offlineRouters > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-white' }}">
                {{ number_format($this->offlineRouters) }}
            </p>
            <flux:text class="mt-1 text-xs text-zinc-400">Enabled but unreachable</flux:text>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Monthly Revenue Chart --}}
        <div class="col-span-2 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">Monthly Revenue</flux:heading>
            <div
                wire:ignore
                x-data="{
                    chart: null,
                    init() {
                        const ctx = this.$refs.revenueCanvas.getContext('2d');
                        const isDark = document.documentElement.classList.contains('dark');
                        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                        const textColor = isDark ? '#a1a1aa' : '#71717a';

                        this.chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: @js($this->monthlyRevenue['labels']),
                                datasets: [{
                                    label: 'Revenue',
                                    data: @js($this->monthlyRevenue['data']),
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59,130,246,0.08)',
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#3b82f6',
                                    tension: 0.4,
                                    fill: true,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => ' ₵' + ctx.parsed.y.toFixed(2)
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { color: gridColor },
                                        ticks: { color: textColor, font: { size: 11 } }
                                    },
                                    y: {
                                        grid: { color: gridColor },
                                        ticks: {
                                            color: textColor,
                                            font: { size: 11 },
                                            callback: (v) => '₵' + v
                                        }
                                    }
                                }
                            }
                        });
                    }
                }"
                class="relative h-64"
            >
                <canvas x-ref="revenueCanvas"></canvas>
            </div>
        </div>

        {{-- Plan Distribution Chart --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">Plan Distribution</flux:heading>
            <div
                wire:ignore
                x-data="{
                    chart: null,
                    init() {
                        const ctx = this.$refs.distCanvas.getContext('2d');
                        this.chart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: @js($this->planDistribution['labels']),
                                datasets: [{
                                    data: @js($this->planDistribution['data']),
                                    backgroundColor: ['#3b82f6'],
                                    borderWidth: 0,
                                    hoverOffset: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '68%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 12,
                                            font: { size: 11 },
                                            color: document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#52525b',
                                            boxWidth: 10,
                                            boxHeight: 10,
                                            borderRadius: 3,
                                            useBorderRadius: true,
                                        }
                                    }
                                }
                            }
                        });
                    }
                }"
                class="relative h-64"
            >
                <canvas x-ref="distCanvas"></canvas>
            </div>
        </div>
    </div>

    {{-- Live Sessions --}}
    <livewire:live-sessions />

    {{-- Recent Transactions --}}
    <div class="mt-6 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-700/60">
            <flux:heading size="sm">Recent Transactions</flux:heading>
            <flux:link :href="route('billing.transactions')" wire:navigate class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400">
                View all →
            </flux:link>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700/60">
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Invoice</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Plan</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Method</th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                    @forelse($this->recentTransactions as $tx)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="px-5 py-3 font-mono text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $tx->invoice }}
                            </td>
                            <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">
                                {{ $tx->customer?->fullname ?? $tx->username }}
                            </td>
                            <td class="px-5 py-3 text-zinc-600 dark:text-zinc-300">
                                {{ $tx->plan_name }}
                            </td>
                            <td class="px-5 py-3 font-semibold text-zinc-900 dark:text-white">
                                ₵{{ number_format($tx->price, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                <flux:badge
                                    size="sm"
                                    :color="match($tx->method) {
                                        'paystack' => 'green',
                                        'card'     => 'blue',
                                        'cash'     => 'zinc',
                                        default    => 'yellow'
                                    }"
                                >
                                    {{ ucfirst($tx->method ?? 'N/A') }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400">
                                {{ $tx->created_at?->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-zinc-400">
                                No transactions yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</flux:main>
