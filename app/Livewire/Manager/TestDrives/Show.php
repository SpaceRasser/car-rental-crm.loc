<?php

namespace App\Livewire\Manager\TestDrives;

use App\Models\TestDrive;
use Livewire\Component;

class Show extends Component
{
    public int $testDriveId;
    public ?string $result_notes = null;

    public function mount(int $testDriveId): void
    {
        $this->testDriveId = $testDriveId;

        $td = $this->td;
        $this->result_notes = $td->result_notes;
    }

    public function getTdProperty(): TestDrive
    {
        return TestDrive::with(['client','car','manager'])->findOrFail($this->testDriveId);
    }

    public function setStatus(string $status): void
    {
        $allowed = ['new','confirmed','completed','no_show','cancelled'];
        if (!in_array($status, $allowed, true)) return;

        $current = $this->td->status;

        $map = [
            'new' => ['confirmed','cancelled'],
            'confirmed' => ['completed','no_show','cancelled'],
            'completed' => [],
            'no_show' => [],
            'cancelled' => [],
        ];

        if (!in_array($status, $map[$current] ?? [], true)) {
            session()->flash('error', "Нельзя сменить статус с '{$current}' на '{$status}'.");
            return;
        }

        $this->td->update(['status' => $status]);
        session()->flash('success', 'Статус обновлён.');
    }

    public function saveResult(): void
    {
        $this->validate([
            'result_notes' => ['nullable','string','max:5000'],
        ]);

        $this->td->update([
            'result_notes' => $this->result_notes,
        ]);

        session()->flash('success', 'Результат сохранён.');
    }

    public function render()
    {
        $statusLabels = [
            'new' => 'Ожидает подтверждения',
            'confirmed' => 'Подтверждён',
            'completed' => 'Завершён',
            'no_show' => 'Не пришёл',
            'cancelled' => 'Отменён',
        ];

        return view('livewire.manager.test-drives.show', [
            'td' => $this->td,
            'statusLabels' => $statusLabels,
        ]);
    }
}
