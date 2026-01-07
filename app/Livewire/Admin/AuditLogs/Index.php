<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $event = '';
    public string $subject = '';
    public ?int $causerId = null;
    public string $from = '';
    public string $to = '';
    public int $perPage = 25;

    protected $queryString = [
        'q' => ['except' => ''],
        'event' => ['except' => ''],
        'subject' => ['except' => ''],
        'causerId' => ['except' => null],
        'from' => ['except' => ''],
        'to' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['q','event','subject','causerId','from','to','perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clear(): void
    {
        $this->q = '';
        $this->event = '';
        $this->subject = '';
        $this->causerId = null;
        $this->from = '';
        $this->to = '';
        $this->perPage = 25;
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()->orderBy('name')->get(['id','name']);

        $events = [
            'created' => 'Создано',
            'updated' => 'Изменено',
            'deleted' => 'Удалено',
            'status_changed' => 'Смена статуса',
            'payment_created' => 'Создан платёж',
            'payment_paid' => 'Платёж PAID',
            'payment_failed' => 'Платёж FAILED',
            'contract_opened' => 'Открыт договор',
            'contract_downloaded' => 'Скачан договор',
        ];

        $subjects = [
            \App\Models\Car::class => 'Автомобили',
            \App\Models\Client::class => 'Клиенты',
            \App\Models\Rental::class => 'Аренды',
            \App\Models\TestDrive::class => 'Тест-драйвы',
            \App\Models\Payment::class => 'Платежи',
            \App\Models\User::class => 'Пользователи',
        ];

        $logs = Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->event !== '', fn($q) => $q->where('event', $this->event))
            ->when($this->subject !== '', fn($q) => $q->where('subject_type', $this->subject))
            ->when($this->causerId, fn($q) => $q->where('causer_id', $this->causerId))
            ->when($this->from !== '', fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->when(trim($this->q) !== '', function ($q) {
                $s = trim($this->q);
                $q->where(function ($qq) use ($s) {
                    $qq->where('description', 'like', "%{$s}%")
                        ->orWhere('log_name', 'like', "%{$s}%")
                        ->orWhere('subject_id', $s)
                        ->orWhere('causer_id', $s);
                });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.audit-logs.index', compact('logs','users','events','subjects'));
    }
}
