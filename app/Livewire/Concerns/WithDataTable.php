<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\Url;
use Livewire\WithPagination;

trait WithDataTable
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'id')]
    public string $sortField = 'id';

    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    public int $perPage = 8;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields(), true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search']);
        $this->resetPage();
    }

    /**
     * Override in each Livewire table component to whitelist sortable SQL columns.
     *
     * @return list<string>
     */
    protected function allowedSortFields(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
