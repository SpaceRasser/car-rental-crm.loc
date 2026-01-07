<?php

namespace App\Livewire\Manager\TestDrives;

use App\Models\Car;
use App\Models\Client;
use App\Models\Rental;
use App\Models\TestDrive;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Form extends Component
{
    public ?int $client_id = null;
    public ?int $car_id = null;

    public string $scheduled_at = '';
    public int $duration_minutes = 60;

    public string $status = 'new';
    public ?string $notes = null;

    public function mount(): void
    {
        $this->client_id = request()->integer('client_id') ?: null;

        $this->scheduled_at = now()->addHour()->format('Y-m-d\TH:i');
    }

    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'car_id' => ['required', 'integer', Rule::exists('cars', 'id')],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'status' => ['required', Rule::in(['new','confirmed','completed','no_show','cancelled'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function interval(): array
    {
        $start = Carbon::parse($this->scheduled_at);
        $end = (clone $start)->addMinutes($this->duration_minutes);
        return [$start, $end];
    }

    private function hasTestDriveOverlap(int $carId, Carbon $start, Carbon $end): bool
    {
        return TestDrive::query()
            ->where('car_id', $carId)
            ->whereIn('status', ['new','confirmed'])
            ->where('scheduled_at', '<', $end)
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$start->format('Y-m-d H:i:s')])
            ->exists();
    }

    private function hasRentalOverlap(int $carId, Carbon $start, Carbon $end): bool
    {
        // если у тебя вместо Rental используется Booking — замени модель тут
        return Rental::query()
            ->where('car_id', $carId)
            ->whereNotIn('status', ['cancelled','closed'])
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    public function save()
    {
        $data = $this->validate();

        [$start, $end] = $this->interval();

        if ($this->hasTestDriveOverlap((int)$data['car_id'], $start, $end)) {
            $this->addError('scheduled_at', 'На это время уже назначен тест-драйв для выбранного авто.');
            return;
        }

        if ($this->hasRentalOverlap((int)$data['car_id'], $start, $end)) {
            $this->addError('scheduled_at', 'На это время авто находится в аренде/броне.');
            return;
        }

        $td = TestDrive::create([
            'client_id' => (int)$data['client_id'],
            'car_id' => (int)$data['car_id'],
            'manager_id' => auth()->id(),
            'scheduled_at' => Carbon::parse($data['scheduled_at']),
            'duration_minutes' => (int)$data['duration_minutes'],
            'status' => $data['status'],
            'notes' => $data['notes'],
        ]);

        session()->flash('success', 'Тест-драйв создан.');
        return redirect()->route('manager.test-drives.show', $td);
    }

    public function render()
    {
        $clients = Client::query()->orderBy('last_name')->orderBy('first_name')->limit(300)->get();
        $cars = Car::query()->where('is_active', true)->orderBy('brand')->orderBy('model')->limit(300)->get();

        $statuses = [
            'new' => 'Ожидает подтверждения',
            'confirmed' => 'Подтверждён',
            'completed' => 'Завершён',
            'no_show' => 'Не пришёл',
            'cancelled' => 'Отменён',
        ];

        return view('livewire.manager.test-drives.form', compact('clients','cars','statuses'));
    }
}
