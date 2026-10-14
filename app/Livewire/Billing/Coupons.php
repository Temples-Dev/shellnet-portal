<?php

namespace App\Livewire\Billing;

use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Coupons extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    // Form
    public ?int $editingId = null;

    public string $fCode = '';

    public string $fType = 'fixed';

    public string $fValue = '';

    public string $fDescription = '';

    public string $fMaxUsage = '';

    public string $fStatus = 'active';

    public string $fMinOrder = '0';

    public string $fMaxDiscount = '';

    public string $fStartsAt = '';

    public string $fExpiresAt = '';

    // Delete
    public ?int $deletingId = null;

    public string $deletingCode = '';

    #[Computed]
    public function coupons()
    {
        return Coupon::when($this->search, fn ($q) => $q
            ->where('code', 'like', "%{$this->search}%")
            ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'fCode', 'fType', 'fValue', 'fDescription',
            'fMaxUsage', 'fStatus', 'fMinOrder', 'fMaxDiscount', 'fStartsAt', 'fExpiresAt']);
        $this->fType = 'fixed';
        $this->fStatus = 'active';
        $this->fMinOrder = '0';
        $this->modal('coupon-form')->show();
    }

    public function openEdit(int $id): void
    {
        $c = Coupon::findOrFail($id);
        $this->editingId = $id;
        $this->fCode = $c->code;
        $this->fType = $c->type;
        $this->fValue = (string) $c->value;
        $this->fDescription = $c->description ?? '';
        $this->fMaxUsage = $c->max_usage !== null ? (string) $c->max_usage : '';
        $this->fStatus = $c->status;
        $this->fMinOrder = (string) $c->min_order_amount;
        $this->fMaxDiscount = $c->max_discount_amount !== null ? (string) $c->max_discount_amount : '';
        $this->fStartsAt = $c->starts_at?->format('Y-m-d') ?? '';
        $this->fExpiresAt = $c->expires_at?->format('Y-m-d') ?? '';
        $this->modal('coupon-form')->show();
    }

    public function save(): void
    {
        $this->validate([
            'fCode' => 'required|string|max:50|unique:coupons,code'.($this->editingId ? ",{$this->editingId}" : ''),
            'fType' => 'required|in:fixed,percent',
            'fValue' => 'required|numeric|min:0',
            'fDescription' => 'nullable|string|max:500',
            'fMaxUsage' => 'nullable|integer|min:1',
            'fStatus' => 'required|in:active,inactive',
            'fMinOrder' => 'nullable|numeric|min:0',
            'fMaxDiscount' => 'nullable|numeric|min:0',
            'fStartsAt' => 'nullable|date',
            'fExpiresAt' => 'nullable|date|after_or_equal:fStartsAt',
        ]);

        $data = [
            'code' => strtoupper(trim($this->fCode)),
            'type' => $this->fType,
            'value' => $this->fValue,
            'description' => $this->fDescription ?: null,
            'max_usage' => $this->fMaxUsage !== '' ? (int) $this->fMaxUsage : null,
            'status' => $this->fStatus,
            'min_order_amount' => $this->fMinOrder ?: 0,
            'max_discount_amount' => $this->fMaxDiscount !== '' ? $this->fMaxDiscount : null,
            'starts_at' => $this->fStartsAt ?: null,
            'expires_at' => $this->fExpiresAt ?: null,
        ];

        if ($this->editingId) {
            Coupon::findOrFail($this->editingId)->update($data);
        } else {
            Coupon::create($data);
        }

        $this->modal('coupon-form')->close();
        unset($this->coupons);
    }

    public function confirmDelete(int $id): void
    {
        $c = Coupon::findOrFail($id);
        $this->deletingId = $id;
        $this->deletingCode = $c->code;
        $this->modal('coupon-delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Coupon::destroy($this->deletingId);
            $this->deletingId = null;
        }
        $this->modal('coupon-delete')->close();
        unset($this->coupons);
    }

    public function toggleStatus(int $id): void
    {
        $c = Coupon::findOrFail($id);
        $c->update(['status' => $c->status === 'active' ? 'inactive' : 'active']);
        unset($this->coupons);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.billing.coupons');
    }
}
