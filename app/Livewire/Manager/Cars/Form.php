<?php

namespace App\Livewire\Manager\Cars;

use App\Models\Car;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?int $carId = null;
    public ?Car $car = null;

    // поля формы
    public string $brand = '';
    public string $model = '';
    public ?int $year = null;
    public ?string $color = null;
    public string $vin = '';
    public string $plate_number = '';
    public ?string $fuel_type = null;
    public ?string $transmission = null;
    public ?int $mileage_km = 0;

    public string $status = 'available';
    public bool $is_active = true;

    public $daily_price = '0.00';
    public $deposit_amount = '0.00';

    public ?string $description = null;

    public function mount(?int $carId = null): void
    {
        $this->carId = $carId;

        if ($carId) {
            $this->car = Car::query()->findOrFail($carId);

            $this->brand = (string) $this->car->brand;
            $this->model = (string) $this->car->model;
            $this->year = $this->car->year;
            $this->color = $this->car->color;

            $this->vin = (string) $this->car->vin;
            $this->plate_number = (string) $this->car->plate_number;

            $this->fuel_type = $this->car->fuel_type;
            $this->transmission = $this->car->transmission;
            $this->mileage_km = (int) ($this->car->mileage_km ?? 0);

            $this->status = (string) $this->car->status;
            $this->is_active = (bool) $this->car->is_active;

            $this->daily_price = (string) $this->car->daily_price;
            $this->deposit_amount = (string) $this->car->deposit_amount;

            $this->description = $this->car->description;
        } else {
            $this->year = now()->year;
        }
    }

    protected function rules(): array
    {
        $carId = $this->car?->id;

        return [
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'year' => ['required', 'integer', 'min:1980', 'max:' . (now()->year + 1)],
            'color' => ['nullable', 'string', 'max:40'],

            'vin' => [
                'required', 'string', 'max:32',
                Rule::unique('cars', 'vin')->ignore($carId)->whereNull('deleted_at'),
            ],
            'plate_number' => [
                'required', 'string', 'max:20',
                Rule::unique('cars', 'plate_number')->ignore($carId)->whereNull('deleted_at'),
            ],

            'fuel_type' => ['nullable', 'string', 'max:30'],
            'transmission' => ['nullable', 'string', 'max:30'],
            'mileage_km' => ['nullable', 'integer', 'min:0'],

            'daily_price' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],

            'status' => ['required', Rule::in(['available', 'rented', 'test_drive', 'maintenance', 'inactive'])],
            'is_active' => ['boolean'],

            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->car) {
            $this->car->update($data);
            session()->flash('success', 'Автомобиль обновлён.');
            return redirect()->route('manager.cars.edit', $this->car);
        }

        $car = Car::create($data);
        session()->flash('success', 'Автомобиль создан.');
        return redirect()->route('manager.cars.edit', $car);
    }

    public function render()
    {
        $statuses = [
            'available'   => 'Доступен',
            'rented'      => 'В аренде',
            'test_drive'  => 'На тест-драйве',
            'maintenance' => 'На обслуживании',
            'inactive'    => 'Отключён',
        ];

        return view('livewire.manager.cars.form', compact('statuses'));
    }
}
