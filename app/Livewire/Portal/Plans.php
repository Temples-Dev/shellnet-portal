<?php

namespace App\Livewire\Portal;

use App\Models\Plan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Plans extends Component
{
    #[Computed]
    public function plans(): \Illuminate\Database\Eloquent\Collection
    {
        return Plan::enabled()
            ->where('type', 'Hotspot')
            ->whereHas('router', fn ($q) => $q->where('enabled', true))
            ->orderBy('price')
            ->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.portal.plans');
    }
}
