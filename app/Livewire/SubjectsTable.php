<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDataTable;
use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SubjectsTable extends Component
{
    use WithDataTable;

    public function mount(): void
    {
        $this->sortField = 'code';
        $this->sortDirection = 'asc';
    }

    protected function allowedSortFields(): array
    {
        return ['id', 'code', 'name', 'credit_hours', 'teachers_count', 'created_at'];
    }

    public function render(): View
    {
        $subjects = Subject::query()
            ->withCount('teachers')
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

        return view('livewire.subjects-table', [
            'subjects' => $subjects,
        ]);
    }
}
