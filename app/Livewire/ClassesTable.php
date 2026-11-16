<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDataTable;
use App\Models\SchoolClass;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ClassesTable extends Component
{
    use WithDataTable;

    public function mount(): void
    {
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    protected function allowedSortFields(): array
    {
        return ['id', 'code', 'name', 'capacity', 'students_count', 'created_at'];
    }

    public function render(): View
    {
        $classes = SchoolClass::query()
            ->withCount('students')
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.classes-table', [
            'classes' => $classes,
        ]);
    }
}
