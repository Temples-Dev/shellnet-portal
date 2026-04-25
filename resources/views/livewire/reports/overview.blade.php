<flux:main>
    <div class="mb-6">
        <flux:heading size="xl">Reports Overview</flux:heading>
        <flux:subheading>Business summary and analytics</flux:subheading>
    </div>

    {{-- KPI Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs text-zinc-400">Total Customers</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->stats['total_customers']) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs text-zinc-400">Active</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($this->stats['active_customers']) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs text-zinc-400">Subscriptions</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($this->stats['active_subscriptions']) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs text-zinc-400">Revenue Today</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">₵{{ number_format($this->stats['revenue_today'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs text-zinc-400">This Month</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">₵{{ number_format($this->stats['revenue_month'], 2) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs text-zinc-400">All-time Revenue</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">₵{{ number_format($this->stats['revenue_total'], 2) }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Revenue Chart --}}
        <div class="col-span-2 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">Revenue — Last 6 Months</flux:heading>
            <div wire:ignore>
                <canvas id="overviewRevenueChart" height="80"></canvas>
            </div>
        </div>

        {{-- Subscription Breakdown --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="sm" class="mb-4">Subscription Status</flux:heading>
            <div wire:ignore>
                <canvas id="overviewSubChart" height="160"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Plans --}}
    <div class="mt-6 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-700/60">
            <flux:heading size="sm">Top Plans by Sales</flux:heading>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-700/60">
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Plan</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Sales</th>
                    <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-400">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                @forelse($this->topPlans as $plan)
                    <tr>
                        <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">{{ $plan->plan_name }}</td>
                        <td class="px-5 py-3 text-zinc-500">{{ number_format($plan->count) }}</td>
                        <td class="px-5 py-3 font-medium text-zinc-900 dark:text-white">₵{{ number_format($plan->revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-zinc-400">No data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</flux:main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const revenueLabels = @js($this->monthlyRevenue['labels']);
    const revenueData   = @js($this->monthlyRevenue['data']);
    const subLabels     = @js($this->subscriptionBreakdown['labels']);
    const subData       = @js($this->subscriptionBreakdown['data']);

    new Chart(document.getElementById('overviewRevenueChart'), {
        type: 'bar',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue ($)',
                data: revenueData,
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderRadius: 4,
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('overviewSubChart'), {
        type: 'doughnut',
        data: {
            labels: subLabels,
            datasets: [{
                data: subData,
                backgroundColor: ['rgba(16,185,129,0.8)', 'rgba(113,113,122,0.5)'],
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
});
</script>
