<?php

namespace App\Livewire\Manager\Rentals;

use App\Models\Rental;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public string $from = ''; // YYYY-MM-DD
    public string $to = '';   // YYYY-MM-DD
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
            'new' => 'Новая',
            'confirmed' => 'Подтверждена',
            'active' => 'Активна',
            'closed' => 'Закрыта',
            'cancelled' => 'Отменена',
            'overdue' => 'Просрочена',
        ];

        $rentals = Rental::query()
            ->with(['car', 'client'])
            ->when($this->q !== '', function ($query) {
                $q = trim($this->q);

                $query->where(function ($qq) use ($q) {
                    // по авто
                    $qq->whereHas('car', function ($qc) use ($q) {
                        $qc->where('brand', 'like', "%{$q}%")
                            ->orWhere('model', 'like', "%{$q}%")
                            ->orWhere('vin', 'like', "%{$q}%")
                            ->orWhere('plate_number', 'like', "%{$q}%");
                    })
                        // по клиенту
                        ->orWhereHas('client', function ($qcl) use ($q) {
                            $qcl->where('first_name', 'like', "%{$q}%")
                                ->orWhere('last_name', 'like', "%{$q}%")
                                ->orWhere('middle_name', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        })
                        // по id аренды
                        ->orWhere('id', $q);
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->from !== '', fn($query) => $query->whereDate('starts_at', '>=', $this->from))
            ->when($this->to !== '', fn($query) => $query->whereDate('ends_at', '<=', $this->to))
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.manager.rentals.index', compact('rentals', 'statuses'));
    }
}
