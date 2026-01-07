<?php

namespace App\Livewire;

use App\Models\Car;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\TestDrive;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Если когда-то зайдёт роль client — пока показываем заглушку
        if ($user?->role === 'client') {
            return view('livewire.dashboard-client');
        }

        $today = Carbon::today();

        // Машины
        $carsTotal = Car::query()->count();
        $carsActive = Car::query()->where('is_active', true)->count();

        // Аренды
        $rentalsNew = Rental::query()->where('status', 'new')->count();
        $rentalsActive = Rental::query()->whereIn('status', ['confirmed', 'active'])->count();
        $rentalsOverdue = Rental::query()->where('status', 'overdue')->count();

        $rentalsEndingSoon = Rental::query()
            ->with(['client', 'car'])
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->whereBetween('ends_at', [now(), now()->addDays(3)])
            ->orderBy('ends_at')
            ->limit(5)
            ->get();

        // Тест-драйвы ближайшие
        $testDrivesUpcoming = TestDrive::query()
            ->with(['client', 'car'])
            ->whereIn('status', ['new', 'confirmed'])
            ->whereBetween('scheduled_at', [now(), now()->addDays(7)])
            ->orderBy('scheduled_at')
            ->limit(7)
            ->get();

        // Неоплачено (суммарно) по арендам
        $paidSub = Payment::query()
            ->selectRaw('rental_id, COALESCE(SUM(amount),0) as paid_amount')
            ->where('status', 'paid')
            ->groupBy('rental_id');

        $unpaidTotal = (float) Rental::query()
            ->leftJoinSub($paidSub, 'p', fn($join) => $join->on('rentals.id', '=', 'p.rental_id'))
            ->whereNotIn('rentals.status', ['cancelled', 'closed'])
            ->selectRaw('COALESCE(SUM(GREATEST(rentals.total_price - COALESCE(p.paid_amount,0), 0)),0) as unpaid_total')
            ->value('unpaid_total');

        $unpaidTop = Rental::query()
            ->with(['client', 'car'])
            ->leftJoinSub($paidSub, 'p', fn($join) => $join->on('rentals.id', '=', 'p.rental_id'))
            ->whereNotIn('rentals.status', ['cancelled', 'closed'])
            ->selectRaw('rentals.*, GREATEST(rentals.total_price - COALESCE(p.paid_amount,0), 0) as remaining')
            ->orderByDesc('remaining')
            ->limit(5)
            ->get();

        // Клиенты
        $clientsTotal = Client::query()->count();
        $clientsNewWeek = Client::query()->where('created_at', '>=', now()->subDays(7))->count();

        return view('livewire.dashboard', compact(
            'carsTotal',
            'carsActive',
            'rentalsNew',
            'rentalsActive',
            'rentalsOverdue',
            'rentalsEndingSoon',
            'testDrivesUpcoming',
            'unpaidTotal',
            'unpaidTop',
            'clientsTotal',
            'clientsNewWeek',
        ));
    }
}
