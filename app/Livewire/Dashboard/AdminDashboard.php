<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Carbon\Carbon;

use App\Models\Car;
use App\Models\Rental;
use App\Models\TestDrive;
use App\Models\Payment;

class AdminDashboard extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $weekTo = (clone $today)->addDays(7);
        $monthFrom = Carbon::now()->startOfMonth();
        $monthTo = Carbon::now()->endOfMonth();

        $stats = [
            'cars_total' => Car::count(),
            'cars_available' => Car::where('status', 'available')->count(),
            'cars_rented' => Car::where('status', 'rented')->count(),
            'cars_maintenance' => Car::where('status', 'maintenance')->count(),

            'rentals_new' => Rental::where('status', 'new')->count(),
            'rentals_active' => Rental::whereIn('status', ['confirmed', 'active', 'overdue'])->count(),
            'rentals_overdue' => Rental::where('status', 'overdue')->count(),

            'test_drives_today' => TestDrive::whereDate('scheduled_at', $today)
                ->whereIn('status', ['new', 'confirmed'])
                ->count(),

            'income_month' => (float) Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$monthFrom, $monthTo])
                ->sum('amount'),
        ];

        $upcomingRentals = Rental::query()
            ->with(['car', 'client'])
            ->whereIn('status', ['new', 'confirmed', 'active', 'overdue'])
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $upcomingTestDrives = TestDrive::query()
            ->with(['car', 'client'])
            ->whereIn('status', ['new', 'confirmed'])
            ->whereBetween('scheduled_at', [$today->copy()->startOfDay(), $weekTo->copy()->endOfDay()])
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        $latestPayments = Payment::query()
            ->with(['rental'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('livewire.dashboard.admin-dashboard', compact('stats', 'upcomingRentals', 'upcomingTestDrives', 'latestPayments'));
    }
}
