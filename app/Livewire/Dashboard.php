<?php

namespace App\Livewire;

use App\Models\Plan;
use App\Models\Router;
use App\Models\Subscription;
use App\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed(cache: true, seconds: 60)]
    public function activeSubscriptions(): int
    {
        return Subscription::active()->count();
    }

    #[Computed(cache: true, seconds: 60)]
    public function revenueToday(): float
    {
        return (float) Transaction::whereDate('created_at', today())->sum('price');
    }

    #[Computed(cache: true, seconds: 60)]
    public function expiringToday(): int
    {
        return Subscription::active()
            ->whereDate('expires_at', today())
            ->count();
    }

    #[Computed(cache: true, seconds: 60)]
    public function offlineRouters(): int
    {
        return Router::enabled()->where('status', 'Offline')->count();
    }

    #[Computed(cache: true, seconds: 60)]
    public function recentTransactions(): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::with('customer')
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed(cache: true, seconds: 60)]
    public function monthlyRevenue(): array
    {
        $months = collect(range(11, 0))->map(function (int $i): array {
            $date  = now()->subMonths($i);
            $total = Transaction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('price');

            return ['label' => $date->format('M y'), 'value' => (float) $total];
        });

        return [
            'labels' => $months->pluck('label')->toArray(),
            'data'   => $months->pluck('value')->toArray(),
        ];
    }

    #[Computed(cache: true, seconds: 60)]
    public function planDistribution(): array
    {
        $counts = Subscription::active()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->selectRaw('plans.type, count(*) as total')
            ->groupBy('plans.type')
            ->pluck('total', 'plans.type');

        $labels = Plan::TYPES;
        $data   = array_map(fn (string $t): int => (int) ($counts[$t] ?? 0), $labels);

        return ['labels' => $labels, 'data' => $data];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.dashboard');
    }
}
