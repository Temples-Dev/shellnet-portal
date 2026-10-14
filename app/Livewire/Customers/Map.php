<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Map extends Component
{
    public string $statusFilter = '';

    public string $serviceFilter = '';

    #[Computed]
    public function geoJson(): array
    {
        $customers = Customer::select('id', 'username', 'fullname', 'coordinates', 'status', 'service_type')
            ->whereNotNull('coordinates')
            ->where('coordinates', '!=', '')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->serviceFilter, fn ($q) => $q->where('service_type', $this->serviceFilter))
            ->get();

        $features = $customers->map(function (Customer $c) {
            $parts = array_map('trim', explode(',', $c->coordinates, 2));

            if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
                return null;
            }

            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $parts[1], (float) $parts[0]], // GeoJSON: [lng, lat]
                ],
                'properties' => [
                    'id' => $c->id,
                    'username' => $c->username,
                    'fullname' => $c->fullname,
                    'status' => $c->status,
                    'service_type' => $c->service_type ?? '',
                    'color' => match ($c->status) {
                        'Active' => '#10b981',
                        'Inactive' => '#71717a',
                        'Banned' => '#ef4444',
                        'Suspended' => '#f59e0b',
                        'Limited' => '#3b82f6',
                        default => '#71717a',
                    },
                ],
            ];
        })->filter()->values()->all();

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    #[Computed]
    public function totalMapped(): int
    {
        return Customer::whereNotNull('coordinates')->where('coordinates', '!=', '')->count();
    }

    public function updatedStatusFilter(): void
    {
        unset($this->geoJson);
        $this->dispatch('map-data-updated', geoJson: $this->geoJson);
    }

    public function updatedServiceFilter(): void
    {
        unset($this->geoJson);
        $this->dispatch('map-data-updated', geoJson: $this->geoJson);
    }

    public function render(): View
    {
        return view('livewire.customers.map');
    }
}
