<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Transaction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Overview extends Component
{
    #[Computed(cache: true, seconds: 120)]
    public function stats(): array
    {
        return [
            'total_customers'      => Customer::count(),
            'active_customers'     => Customer::where('status', 'Active')->count(),
            'active_subscriptions' => Subscription::where('status', 'on')->count(),
            'revenue_today'        => (float) Transaction::whereDate('created_at', today())->sum('price'),
            'revenue_month'        => (float) Transaction::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)->sum('price'),
            'revenue_total'        => (float) Transaction::sum('price'),
        ];
    }

    #[Computed(cache: true, seconds: 120)]
    public function monthlyRevenue(): array
    {
        $months = collect(range(5, 0))->map(function (int $i) {
            $date  = now()->subMonths($i);
            $total = (float) Transaction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('price');

            return ['label' => $date->format('M Y'), 'total' => $total];
        });

        return [
            'labels' => $months->pluck('label')->all(),
            'data'   => $months->pluck('total')->all(),
        ];
    }

    #[Computed(cache: true, seconds: 120)]
    public function topPlans(): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::selectRaw('plan_name, COUNT(*) as count, SUM(price) as revenue')
            ->groupBy('plan_name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();
    }

    #[Computed(cache: true, seconds: 120)]
    public function subscriptionBreakdown(): array
    {
        return [
            'labels' => ['Active', 'Expired'],
            'data'   => [
                Subscription::where('status', 'on')->count(),
                Subscription::where('status', 'off')->count(),
            ],
        ];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.overview');
    }
}
