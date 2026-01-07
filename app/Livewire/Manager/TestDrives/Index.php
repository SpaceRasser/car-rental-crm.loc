<?php

namespace App\Livewire\Manager\TestDrives;

use App\Models\TestDrive;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public string $from = '';
    public string $to = '';
    public int $perPage = 15;

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['q', 'status', 'from', 'to', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->from = '';
        $this->to = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function render()
    {
        $statuses = [
            'new' => 'Ожидает подтверждения',
            'confirmed' => 'Подтверждён',
            'completed' => 'Завершён',
            'no_show' => 'Не пришёл',
            'cancelled' => 'Отменён',
        ];

        $items = TestDrive::query()
            ->with(['client', 'car', 'manager'])
            ->when($this->q !== '', function ($query) {
                $q = trim($this->q);

                $query->where(function ($qq) use ($q) {
                    $qq->whereHas('client', function ($qc) use ($q) {
                        $qc->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%")
                            ->orWhere('middle_name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })->orWhereHas('car', function ($qcar) use ($q) {
                        $qcar->where('brand', 'like', "%{$q}%")
                            ->orWhere('model', 'like', "%{$q}%")
                            ->orWhere('vin', 'like', "%{$q}%")
                            ->orWhere('plate_number', 'like', "%{$q}%");
                    })->orWhere('id', $q);
                });
            })
            ->when($this->status !== '', fn($q) => $q->where('status', $this->status))
            ->when($this->from !== '', fn($q) => $q->whereDate('scheduled_at', '>=', $this->from))
            ->when($this->to !== '', fn($q) => $q->whereDate('scheduled_at', '<=', $this->to))
            ->orderByDesc('scheduled_at')
            ->paginate($this->perPage);

        return view('livewire.manager.test-drives.index', compact('items', 'statuses'));
    }
}
