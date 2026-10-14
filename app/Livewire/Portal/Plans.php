<?php

namespace App\Livewire\Portal;

use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Plans extends Component
{
    #[Computed]
    public function plans(): Collection
    {
        return Plan::enabled()
            ->where('type', 'Hotspot')
            ->whereHas('router', fn ($q) => $q->where('enabled', true))
            ->orderBy('price')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.portal.plans');
    }
}
