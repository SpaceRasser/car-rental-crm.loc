<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Carbon\Carbon;

use App\Models\Rental;
use App\Models\TestDrive;

class ManagerDashboard extends Component
{
    public function render()
    {
        $userId = auth()->id();

        $today = Carbon::today();
        $tomorrow = (clone $today)->addDay();
        $weekTo = (clone $today)->addDays(7);

        $stats = [
            'new_rentals' => Rental::where('manager_id', $userId)->where('status', 'new')->count(),
            'active_rentals' => Rental::where('manager_id', $userId)->whereIn('status', ['confirmed', 'active', 'overdue'])->count(),
            'overdue' => Rental::where('manager_id', $userId)->where('status', 'overdue')->count(),
            'td_today' => TestDrive::where('manager_id', $userId)
                ->whereDate('scheduled_at', $today)
                ->whereIn('status', ['new', 'confirmed'])
                ->count(),
        ];

        $pickupsToday = Rental::query()
            ->with(['car', 'client'])
            ->where('manager_id', $userId)
            ->whereDate('starts_at', $today)
            ->whereIn('status', ['new', 'confirmed', 'active'])
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        $returnsToday = Rental::query()
            ->with(['car', 'client'])
            ->where('manager_id', $userId)
            ->whereDate('ends_at', $today)
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('ends_at')
            ->limit(10)
            ->get();

        $testDrivesSoon = TestDrive::query()
            ->with(['car', 'client'])
            ->where('manager_id', $userId)
            ->whereIn('status', ['new', 'confirmed'])
            ->whereBetween('scheduled_at', [$today->copy()->startOfDay(), $weekTo->copy()->endOfDay()])
            ->orderBy('scheduled_at')
            ->limit(10)
            ->get();

        return view('livewire.dashboard.manager-dashboard', compact('stats', 'pickupsToday', 'returnsToday', 'testDrivesSoon'));
    }
}
