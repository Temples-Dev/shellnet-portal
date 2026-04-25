<?php

namespace App\Livewire\Portal;

use App\Models\Plan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Plans extends Component
{
    public string $type = 'Hotspot';

    #[Computed]
    public function plans(): \Illuminate\Database\Eloquent\Collection
    {
        return Plan::enabled()
            ->where('type', $this->type)
            ->whereHas('router', fn ($q) => $q->where('enabled', true))
            ->orderBy('price')
            ->get();
    }

    #[Computed]
    public function availableTypes(): array
    {
        return Plan::enabled()
            ->whereHas('router', fn ($q) => $q->where('enabled', true))
            ->distinct()
            ->pluck('type')
            ->toArray();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.portal.plans');
    }
}
