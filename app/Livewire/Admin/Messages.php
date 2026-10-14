<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\CustomerMessage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Messages extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $unreadOnly = false;

    // Compose
    public string $composeCustomerId = '';

    public string $composeSubject = '';

    public string $composeBody = '';

    // View
    public ?int $viewingId = null;

    #[Computed]
    public function messages()
    {
        return CustomerMessage::with('customer', 'admin')
            ->when($this->search, fn ($q) => $q
                ->where('subject', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($cq) => $cq
                    ->where('fullname', 'like', "%{$this->search}%")
                    ->orWhere('username', 'like', "%{$this->search}%")))
            ->when($this->unreadOnly, fn ($q) => $q->unread())
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function customers()
    {
        return Customer::orderBy('fullname')->get(['id', 'fullname', 'username']);
    }

    #[Computed]
    public function viewingMessage(): ?CustomerMessage
    {
        return $this->viewingId
            ? CustomerMessage::with('customer', 'admin')->find($this->viewingId)
            : null;
    }

    #[Computed]
    public function unreadCount(): int
    {
        return CustomerMessage::unread()->count();
    }

    public function openCompose(): void
    {
        $this->reset(['composeCustomerId', 'composeSubject', 'composeBody']);
        $this->modal('message-compose')->show();
    }

    public function send(): void
    {
        $this->validate([
            'composeCustomerId' => 'required|exists:customers,id',
            'composeSubject' => 'required|string|max:255',
            'composeBody' => 'required|string|max:5000',
        ]);

        CustomerMessage::create([
            'customer_id' => (int) $this->composeCustomerId,
            'admin_id' => auth()->id(),
            'subject' => $this->composeSubject,
            'body' => $this->composeBody,
            'from' => 'admin',
        ]);

        $this->modal('message-compose')->close();
        unset($this->messages, $this->unreadCount);
    }

    public function view(int $id): void
    {
        $this->viewingId = $id;
        CustomerMessage::where('id', $id)->whereNull('read_at')->update(['read_at' => now()]);
        $this->modal('message-view')->show();
        unset($this->messages, $this->unreadCount, $this->viewingMessage);
    }

    public function delete(int $id): void
    {
        CustomerMessage::destroy($id);
        unset($this->messages, $this->unreadCount);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUnreadOnly(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.admin.messages');
    }
}
