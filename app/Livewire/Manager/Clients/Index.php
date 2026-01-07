<?php

namespace App\Livewire\Manager\Clients;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = '';      // reliability_status
    public string $verified = '';    // '' | '1' | '0'
    public int $perPage = 15;

    protected $queryString = [
        'q' => ['except' => ''],
        'status' => ['except' => ''],
        'verified' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['q', 'status', 'verified', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->status = '';
        $this->verified = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->q !== '', function ($query) {
                $q = trim($this->q);
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('middle_name', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('driver_license_number', 'like', "%{$q}%");
                });
            })
            ->when($this->status !== '', fn($query) => $query->where('reliability_status', $this->status))
            ->when($this->verified !== '', fn($query) => $query->where('is_verified', (bool) (int) $this->verified))
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $statuses = [
            'normal' => 'Обычный',
            'vip' => 'VIP',
            'blocked' => 'Заблокирован',
        ];

        return view('livewire.manager.clients.index', compact('clients', 'statuses'));
    }
}
