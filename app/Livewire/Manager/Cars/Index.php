<?php

namespace App\Livewire\Manager\Cars;

use App\Models\Car;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';
    public string $brand = '';
    public ?int $year = null;

    public int $perPage = 15;

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'brand' => ['except' => ''],
        'year' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        // при изменении фильтров возвращаемся на 1 страницу
        if (in_array($name, ['q', 'status', 'brand', 'year', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->brand = '';
        $this->year = null;
        $this->perPage = 15;
        $this->resetPage();
    }

    public function render()
    {
        $cars = Car::query()
            ->with(['mainPhoto'])
            ->when($this->q !== '', function ($query) {
                $q = trim($this->q);
                $query->where(function ($qq) use ($q) {
                    $qq->where('brand', 'like', "%{$q}%")
                        ->orWhere('model', 'like', "%{$q}%")
                        ->orWhere('vin', 'like', "%{$q}%")
                        ->orWhere('plate_number', 'like', "%{$q}%");
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('status', $this->status))
            ->when($this->brand !== '', fn($query) => $query->where('brand', $this->brand))
            ->when(!is_null($this->year), fn($query) => $query->where('year', $this->year))
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $brands = Car::query()
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->toArray();

        $years = Car::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();

        $statuses = [
            'available' => 'Available',
            'rented' => 'Rented',
            'test_drive' => 'Test drive',
            'maintenance' => 'Maintenance',
            'inactive' => 'Inactive',
        ];

        return view('livewire.manager.cars.index', compact('cars', 'brands', 'years', 'statuses'))
            ->layoutData(['header' => 'Cars']);
    }
}
