<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithDataTable;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class StudentsTable extends Component
{
    use WithDataTable;

    #[Url(as: 'class_id', except: '')]
    public string $classId = '';

    #[Url(except: '')]
    public string $status = '';

    public function updatingClassId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'classId', 'status']);
        $this->resetPage();
    }

    protected function allowedSortFields(): array
    {
        return ['id', 'student_code', 'name', 'class_name', 'status', 'created_at'];
    }

    public function render(): View
    {
        $sortColumn = match ($this->sortField) {
            'student_code' => 'tbl_students.student_code',
            'name' => 'tbl_students.name',
            'class_name' => 'tbl_classes.name',
            'status' => 'tbl_students.status',
            'created_at' => 'tbl_students.created_at',
            default => 'tbl_students.id',
        };

        $students = Student::query()
            ->select('tbl_students.*')
            ->leftJoin('tbl_classes', 'tbl_students.class_id', '=', 'tbl_classes.id')
            ->leftJoin('tbl_users', 'tbl_students.user_id', '=', 'tbl_users.id')
            ->with(['schoolClass', 'user'])
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('tbl_students.name', 'like', $term)
                        ->orWhere('tbl_students.student_code', 'like', $term)
                        ->orWhere('tbl_users.email', 'like', $term)
                        ->orWhere('tbl_classes.name', 'like', $term);
                });
            })
            ->when($this->classId !== '', fn ($q) => $q->where('tbl_students.class_id', $this->classId))
            ->when($this->status !== '', fn ($q) => $q->where('tbl_students.status', $this->status))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.students-table', [
            'students' => $students,
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }
}
