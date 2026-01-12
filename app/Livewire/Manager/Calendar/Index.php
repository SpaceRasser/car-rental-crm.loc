<?php

namespace App\Livewire\Manager\Calendar;

use App\Models\Rental;
use App\Models\TestDrive;
use Carbon\Carbon;
use Livewire\Component;

class Index extends Component
{
    public string $month;        // YYYY-MM
    public string $selectedDate; // YYYY-MM-DD

    public bool $showRentals = true;
    public bool $showTestDrives = true;

    public function mount(): void
    {
        $today = now();
        $this->month = $today->format('Y-m');
        $this->selectedDate = $today->format('Y-m-d');
    }

    public function prevMonth(): void
    {
        $m = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->subMonthNoOverflow();
        $this->month = $m->format('Y-m');
        $this->selectedDate = $m->format('Y-m-d'); // чтобы всегда был день в текущем месяце
    }

    public function nextMonth(): void
    {
        $m = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->addMonthNoOverflow();
        $this->month = $m->format('Y-m');
        $this->selectedDate = $m->format('Y-m-d');
    }

    public function goToday(): void
    {
        $today = now();
        $this->month = $today->format('Y-m');
        $this->selectedDate = $today->format('Y-m-d');
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    private function monthRange(): array
    {
        $m = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();

        $start = (clone $m)->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end   = (clone $m)->endOfMonth()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        return [$start, $end];
    }

    private function buildDays(Carbon $start, Carbon $end): array
    {
        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return $days;
    }

    private function eventsMap(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $map = []; // ['Y-m-d' => ['rentals'=>[], 'test_drives'=>[]]]

        $init = function (string $key) use (&$map) {
            if (!isset($map[$key])) {
                $map[$key] = ['rentals' => [], 'test_drives' => []];
            } else {
                $map[$key]['rentals'] ??= [];
                $map[$key]['test_drives'] ??= [];
            }
        };

        // Аренды: пересекают диапазон
        if ($this->showRentals) {
            $rentals = Rental::query()
                ->with(['client', 'car'])
                ->whereNotIn('status', ['cancelled', 'closed'])
                ->where('starts_at', '<=', $rangeEnd)
                ->where('ends_at', '>=', $rangeStart)
                ->orderBy('starts_at')
                ->get();

            foreach ($rentals as $r) {
                $start = Carbon::parse($r->starts_at)->startOfDay();
                $end   = Carbon::parse($r->ends_at)->endOfDay();

                // ✅ без Carbon::max/min (у Carbon нет таких методов)
                $from = $start->lt($rangeStart) ? $rangeStart->copy()->startOfDay() : $start;
                $to   = $end->gt($rangeEnd)     ? $rangeEnd->copy()->endOfDay()     : $end;

                $d = $from->copy()->startOfDay();
                while ($d->lte($to)) {
                    $key = $d->format('Y-m-d');
                    $init($key);
                    $map[$key]['rentals'][] = $r;
                    $d->addDay();
                }
            }
        }

        // Тест-драйвы: внутри диапазона
        if ($this->showTestDrives) {
            $testDrives = TestDrive::query()
                ->with(['client', 'car'])
                ->whereIn('status', ['new', 'confirmed'])
                ->whereBetween('scheduled_at', [$rangeStart, $rangeEnd])
                ->orderBy('scheduled_at')
                ->get();

            foreach ($testDrives as $t) {
                $key = Carbon::parse($t->scheduled_at)->format('Y-m-d');
                $init($key);
                $map[$key]['test_drives'][] = $t;
            }
        }

        return $map;
    }

    public function render()
    {
        [$rangeStart, $rangeEnd] = $this->monthRange();

        $monthObj = Carbon::createFromFormat('Y-m', $this->month);
        $days = $this->buildDays($rangeStart, $rangeEnd);
        $events = $this->eventsMap($rangeStart, $rangeEnd);

        $selected = Carbon::createFromFormat('Y-m-d', $this->selectedDate);
        $selectedKey = $selected->format('Y-m-d');

        $selectedRentals = $events[$selectedKey]['rentals'] ?? [];
        $selectedTestDrives = $events[$selectedKey]['test_drives'] ?? [];

        $weekdays = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];

        $tdStatus = [
            'new' => 'Ожидает',
            'confirmed' => 'Подтверждён',
            'completed' => 'Завершён',
            'no_show' => 'Не пришёл',
            'cancelled' => 'Отменён',
        ];

        $rentalStatus = [
            'new' => 'Новая',
            'confirmed' => 'Подтверждена',
            'active' => 'Активна',
            'closed' => 'Закрыта',
            'cancelled' => 'Отменена',
            'overdue' => 'Просрочена',
        ];

        return view('livewire.manager.calendar.index', compact(
            'monthObj',
            'days',
            'events',
            'weekdays',
            'selected',
            'selectedRentals',
            'selectedTestDrives',
            'tdStatus',
            'rentalStatus'
        ));
    }
}
