<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDataTable;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class TeachersTable extends Component
{
    use WithDataTable;

    #[Url(as: 'subject_id', except: '')]
    public string $subjectId = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    public function updatingSubjectId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'subjectId', 'status']);
        $this->resetPage();
    }

    protected function allowedSortFields(): array
    {
        return ['id', 'teacher_code', 'name', 'subject_name', 'status', 'created_at'];
    }

    public function render(): View
    {
        $sortColumn = match ($this->sortField) {
            'teacher_code' => 'tbl_teachers.teacher_code',
            'subject_name' => 'tbl_subjects.name',
            'status' => 'tbl_teachers.status',
            'created_at' => 'tbl_teachers.created_at',
            'id' => 'tbl_teachers.id',
            default => 'tbl_teachers.name',
        };

        $teachers = Teacher::query()
            ->select('tbl_teachers.*')
            ->leftJoin('tbl_subjects', 'tbl_teachers.subject_id', '=', 'tbl_subjects.id')
            ->leftJoin('tbl_users', 'tbl_teachers.user_id', '=', 'tbl_users.id')
            ->with(['subject', 'user'])
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('tbl_teachers.name', 'like', $term)
                        ->orWhere('tbl_teachers.teacher_code', 'like', $term)
                        ->orWhere('tbl_teachers.phone', 'like', $term)
                        ->orWhere('tbl_subjects.name', 'like', $term);
                });
            })
            ->when($this->subjectId !== '', fn ($q) => $q->where('tbl_teachers.subject_id', $this->subjectId))
            ->when($this->status !== '', fn ($q) => $q->where('tbl_teachers.status', $this->status))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teachers-table', [
            'teachers' => $teachers,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }
}
