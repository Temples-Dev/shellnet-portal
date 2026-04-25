<?php

namespace App\Livewire\Reports;

use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Transaction;
use Livewire\Component;

class Export extends Component
{
    public string $entity       = 'transactions';
    public string $dateFrom     = '';
    public string $dateTo       = '';
    public string $statusFilter = '';

    public const ENTITIES = [
        'transactions'  => 'Transactions',
        'customers'     => 'Customers',
        'subscriptions' => 'Subscriptions',
    ];

    public function export(): mixed
    {
        $this->validate([
            'entity'   => 'required|in:transactions,customers,subscriptions',
            'dateFrom' => 'nullable|date',
            'dateTo'   => 'nullable|date|after_or_equal:dateFrom',
        ]);

        return match ($this->entity) {
            'transactions'  => $this->exportTransactions(),
            'customers'     => $this->exportCustomers(),
            'subscriptions' => $this->exportSubscriptions(),
        };
    }

    private function exportTransactions(): mixed
    {
        $rows = Transaction::with('customer')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->get();

        $headers = ['Invoice', 'Customer', 'Username', 'Plan', 'Amount', 'Method', 'Date'];
        $data    = $rows->map(fn ($r) => [
            $r->invoice,
            $r->customer?->fullname ?? '',
            $r->username,
            $r->plan_name,
            number_format($r->price, 2),
            $r->method ?? '',
            $r->created_at?->format('Y-m-d H:i') ?? '',
        ]);

        return $this->streamCsv('transactions', $headers, $data);
    }

    private function exportCustomers(): mixed
    {
        $rows = Customer::when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->get();

        $headers = ['ID', 'Full Name', 'Username', 'Email', 'Phone', 'Service', 'Status', 'Created'];
        $data    = $rows->map(fn ($r) => [
            $r->id,
            $r->fullname,
            $r->username,
            $r->email ?? '',
            $r->phone ?? '',
            $r->service_type ?? '',
            $r->status,
            $r->created_at->format('Y-m-d'),
        ]);

        return $this->streamCsv('customers', $headers, $data);
    }

    private function exportSubscriptions(): mixed
    {
        $rows = Subscription::with('customer', 'plan')
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->get();

        $headers = ['ID', 'Customer', 'Username', 'Plan', 'Status', 'Recharged At', 'Expires At'];
        $data    = $rows->map(fn ($r) => [
            $r->id,
            $r->customer?->fullname ?? '',
            $r->username,
            $r->plan_name,
            $r->status,
            $r->recharged_at?->format('Y-m-d H:i') ?? '',
            $r->expired_at?->format('Y-m-d H:i') ?? '',
        ]);

        return $this->streamCsv('subscriptions', $headers, $data);
    }

    private function streamCsv(string $name, array $headers, \Illuminate\Support\Collection $rows): mixed
    {
        $filename = $name . '-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $fp = fopen('php://output', 'w');
            fputcsv($fp, $headers);
            foreach ($rows as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.export');
    }
}
