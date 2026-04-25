<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Staff extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';

    public string $fName = '';
    public string $fEmail = '';
    public string $fPassword = '';
    public string $fUserType = 'Agent';
    public string $fStatus = 'Active';

    public ?int $editingId = null;
    public ?int $deletingId = null;
    public string $deletingName = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedRoleFilter(): void { $this->resetPage(); }

    #[Computed]
    public function staff()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn ($q) => $q->where('user_type', $this->roleFilter))
            ->orderBy('name')
            ->paginate(15);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetErrorBag();
        $this->modal('staff-form')->show();
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId  = $id;
        $this->fName      = $user->name;
        $this->fEmail     = $user->email;
        $this->fPassword  = '';
        $this->fUserType  = $user->user_type ?? 'Agent';
        $this->fStatus    = $user->status ?? 'Active';
        $this->resetErrorBag();
        $this->modal('staff-form')->show();
    }

    public function save(): void
    {
        $rules = [
            'fName'     => 'required|string|max:128',
            'fEmail'    => 'required|email|unique:users,email' . ($this->editingId ? ",{$this->editingId}" : ''),
            'fUserType' => 'required|in:' . implode(',', User::USER_TYPES),
            'fStatus'   => 'required|in:' . implode(',', User::STATUSES),
        ];

        if (! $this->editingId) {
            $rules['fPassword'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $data = [
            'name'      => $this->fName,
            'email'     => $this->fEmail,
            'user_type' => $this->fUserType,
            'status'    => $this->fStatus,
        ];

        if ($this->fPassword !== '') {
            $data['password'] = bcrypt($this->fPassword);
        }

        if ($this->editingId) {
            // Prevent downgrading another SuperAdmin
            $target = User::findOrFail($this->editingId);
            if ($target->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
                $this->addError('fUserType', 'Only a SuperAdmin can modify another SuperAdmin.');
                return;
            }
            $target->update($data);
        } else {
            User::create(array_merge($data, ['email_verified_at' => now()]));
        }

        $this->modal('staff-form')->close();
        $this->resetForm();
        $this->editingId = null;
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            return; // Can't delete yourself
        }
        $user = User::findOrFail($id);
        $this->deletingId   = $id;
        $this->deletingName = $user->name;
        $this->modal('staff-delete')->show();
    }

    public function delete(): void
    {
        if ($this->deletingId && $this->deletingId !== auth()->id()) {
            User::findOrFail($this->deletingId)->delete();
        }
        $this->deletingId = null;
        $this->modal('staff-delete')->close();
    }

    private function resetForm(): void
    {
        $this->fName     = '';
        $this->fEmail    = '';
        $this->fPassword = '';
        $this->fUserType = 'Agent';
        $this->fStatus   = 'Active';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.admin.staff');
    }
}
