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
            ->when(true, function ($query) {
                $groupIds = Rental::query()
                    ->selectRaw('MIN(id) as id')
                    ->whereNotNull('group_uuid')
                    ->groupBy('group_uuid');

                $query->where(function ($q) use ($groupIds) {
                    $q->whereNull('group_uuid')
                        ->orWhereIn('id', $groupIds);
                });
            })
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

        $groupUuids = $rentals->pluck('group_uuid')->filter()->unique()->values();
        $groupTotals = collect();

        if ($groupUuids->isNotEmpty()) {
            $groupRentals = Rental::query()
                ->with(['car'])
                ->whereIn('group_uuid', $groupUuids)
                ->get()
                ->groupBy('group_uuid');

            $groupTotals = $groupRentals->map(function ($items) {
                $grand = (float) $items->sum(fn($item) => (float) ($item->grand_total ?? 0));
                $deposit = (float) $items->sum(fn($item) => (float) ($item->deposit_amount ?? 0));

                return [
                    'total' => round($grand + $deposit, 2),
                    'cars_count' => $items->count(),
                    'cars' => $items->map(function ($item) {
                        return [
                            'label' => trim(($item->car?->brand ?? '').' '.($item->car?->model ?? '')),
                            'plate' => $item->car?->plate_number,
                            'is_trusted' => (bool) $item->is_trusted_person,
                        ];
                    })->values(),
                ];
            });
        }

        return view('livewire.manager.rentals.index', compact('rentals', 'statuses', 'groupTotals'));
    }
}
