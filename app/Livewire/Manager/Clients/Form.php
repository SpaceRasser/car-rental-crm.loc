<?php

namespace App\Livewire\Manager\Clients;

use App\Models\Client;
use App\Models\ClientCarAssignment;
use App\Models\Car;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Form extends Component
{
    public ?int $clientId = null;
    public ?Client $client = null;

    public string $first_name = '';
    public string $last_name = '';
    public ?string $middle_name = null;
    public ?string $phone = null;
    public ?string $email = null;

    public ?string $driver_license_number = null;
    public ?string $driver_license_issued_at = null;   // Y-m-d
    public ?string $driver_license_expires_at = null;  // Y-m-d
    public ?string $birth_date = null;                 // Y-m-d

    public string $reliability_status = 'normal';
    public bool $is_verified = false;
    public ?string $notes = null;
    public ?string $trusted_person_name = null;
    public ?string $trusted_person_phone = null;
    public ?string $trusted_person_license_number = null;

    public function mount(?int $clientId = null): void
    {
        $this->clientId = $clientId;

        if ($clientId) {
            $this->client = Client::findOrFail($clientId);

            $this->first_name = $this->client->first_name;
            $this->last_name = $this->client->last_name;
            $this->middle_name = $this->client->middle_name;

            $this->phone = $this->client->phone;
            $this->email = $this->client->email;

            $this->driver_license_number = $this->client->driver_license_number;
            $this->driver_license_issued_at = optional($this->client->driver_license_issued_at)->format('Y-m-d');
            $this->driver_license_expires_at = optional($this->client->driver_license_expires_at)->format('Y-m-d');
            $this->birth_date = optional($this->client->birth_date)->format('Y-m-d');

            $this->reliability_status = $this->client->reliability_status;
            $this->is_verified = (bool) $this->client->is_verified;
            $this->notes = $this->client->notes;
            $this->trusted_person_name = $this->client->trusted_person_name;
            $this->trusted_person_phone = $this->client->trusted_person_phone;
            $this->trusted_person_license_number = $this->client->trusted_person_license_number;

        }
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'middle_name' => ['nullable', 'string', 'max:80'],

            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],

            'driver_license_number' => ['nullable', 'string', 'max:50'],
            'driver_license_issued_at' => ['nullable', 'date'],
            'driver_license_expires_at' => ['nullable', 'date', 'after_or_equal:driver_license_issued_at'],
            'birth_date' => ['nullable', 'date'],

            'reliability_status' => ['required', Rule::in(['normal', 'vip', 'blocked'])],
            'is_verified' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'trusted_person_name' => ['nullable', 'string', 'max:120'],
            'trusted_person_phone' => ['nullable', 'string', 'max:30'],
            'trusted_person_license_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function save()
    {
        $data = $this->validate();
        $clientCarIds = array_values(array_unique(array_map('intval', $data['client_car_ids'] ?? [])));
        $trustedCarIds = array_values(array_unique(array_map('intval', $data['trusted_car_ids'] ?? [])));

        unset($data['client_car_ids'], $data['trusted_car_ids']);

        if ($this->client) {
            $this->client->update($data);
            $this->syncCars($this->client, $clientCarIds, $trustedCarIds);
            session()->flash('success', 'Клиент обновлён.');
            return redirect()->route('manager.clients.show', $this->client);
        }

        $data['created_by'] = auth()->id();
        $client = Client::create($data);
        $this->syncCars($client, $clientCarIds, $trustedCarIds);

        session()->flash('success', 'Клиент создан.');
        return redirect()->route('manager.clients.show', $client);
    }

    public function render()
    {
        $statuses = [
            'normal' => 'Обычный',
            'vip' => 'VIP',
            'blocked' => 'Заблокирован',
        ];

        $cars = Car::query()
            ->where('is_active', true)
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        return view('livewire.manager.clients.form', compact('statuses', 'cars'));
    }

    private function syncCars(Client $client, array $clientCarIds, array $trustedCarIds): void
    {
        $client->carAssignments()->delete();

        foreach ($clientCarIds as $carId) {
            ClientCarAssignment::create([
                'client_id' => $client->id,
                'car_id' => $carId,
                'relation_type' => 'client',
            ]);
        }

        foreach ($trustedCarIds as $carId) {
            ClientCarAssignment::create([
                'client_id' => $client->id,
                'car_id' => $carId,
                'relation_type' => 'trusted',
            ]);
        }
    }
}
