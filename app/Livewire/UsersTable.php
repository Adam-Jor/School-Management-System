<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDataTable;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class UsersTable extends Component
{
    use WithDataTable;

    #[Url(except: '')]
    public string $role = '';

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'role']);
        $this->resetPage();
    }

    protected function allowedSortFields(): array
    {
        return ['id', 'name', 'username', 'role', 'status', 'last_login_time'];
    }

    public function render(): View
    {
        $users = User::query()
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('username', 'like', $term);
                });
            })
            ->when($this->role !== '', fn ($q) => $q->where('role', $this->role))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.users-table', [
            'users' => $users,
        ]);
    }
}
