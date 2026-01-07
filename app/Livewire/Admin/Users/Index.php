<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public string $role = '';
    public string $active = ''; // '', '1', '0'
    public int $perPage = 15;

    protected $queryString = [
        'q' => ['except' => ''],
        'role' => ['except' => ''],
        'active' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['q', 'role', 'active', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }

    public function setRole(int $userId, string $role): void
    {
        $this->ensureAdmin();

        $allowed = ['admin', 'manager', 'client'];
        if (!in_array($role, $allowed, true)) return;

        /** @var User $u */
        $u = User::query()->findOrFail($userId);

        // чтобы админ сам себя случайно не “разадминил”
        if ($u->id === auth()->id() && $role !== 'admin') {
            session()->flash('err', 'Нельзя снять роль admin у самого себя.');
            return;
        }

        $u->update(['role' => $role]);
        session()->flash('ok', "Роль обновлена: {$u->email} → {$role}");
    }

    public function toggleActive(int $userId): void
    {
        $this->ensureAdmin();

        /** @var User $u */
        $u = User::query()->findOrFail($userId);

        // чтобы админ сам себя не выключил
        if ($u->id === auth()->id()) {
            session()->flash('err', 'Нельзя отключить самого себя.');
            return;
        }

        $u->update(['is_active' => !$u->is_active]);
        session()->flash('ok', $u->is_active ? 'Пользователь активирован.' : 'Пользователь отключён.');
    }

    public function render()
    {
        $this->ensureAdmin();

        $roles = [
            'admin' => 'Администратор',
            'manager' => 'Менеджер',
            'client' => 'Клиент',
        ];

        $users = User::query()
            ->when($this->q !== '', function ($q) {
                $s = trim($this->q);
                $q->where(function ($qq) use ($s) {
                    $qq->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhere('id', $s);
                });
            })
            ->when($this->role !== '', fn($q) => $q->where('role', $this->role))
            ->when($this->active !== '', fn($q) => $q->where('is_active', (int)$this->active))
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.users.index', compact('users', 'roles'));
    }
}
