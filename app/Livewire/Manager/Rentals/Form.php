<?php

namespace App\Livewire\Manager\Rentals;

use App\Models\Car;
use App\Models\Client;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\Extra;
use Illuminate\Support\Str;

class Form extends Component
{
    public ?int $client_id = null;
    public array $carBlocks = [];

    public string $starts_at = '';
    public string $ends_at = '';

    public string $status = 'new';
    public ?string $notes = null;

    // цены (подставляем из авто)
    public string $daily_price = '0.00';
    public string $deposit_amount = '0.00';

    // подсчёт
    public int $days = 0;
    public string $rent_amount = '0.00';
    public string $total_amount = '0.00';
    public array $selectedExtras = [];
    public string $extras_amount = '0.00';
    public array $carSummaries = [];
    public array $mandatoryExtraIds = [];

    public bool $overridePricing = false;

    public function mount(): void
    {
        $this->starts_at = request('starts_at') ?: '';
        $this->ends_at   = request('ends_at')   ?: '';

        $this->client_id = request()->integer('client_id') ?: $this->client_id;

        if (empty($this->carBlocks)) {
            $this->carBlocks[] = $this->blankCarBlock();
        }

        $this->mandatoryExtraIds = Extra::query()
            ->where('is_active', true)
            ->where('name', 'like', '%страх%')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        $this->applyMandatoryExtras();
        $this->recalc();
    }

    public function updated($name): void
    {
        if (
            in_array($name, ['starts_at', 'ends_at', 'daily_price', 'deposit_amount'], true)
            || str_starts_with($name, 'selectedExtras')
            || str_starts_with($name, 'carBlocks')
        ) {
            if ($name === 'carBlocks.0.car_id') {
                $this->syncPrimaryCarPricing();
            }
            $this->recalc();
        }
    }


    protected function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'carBlocks' => ['required', 'array', 'min:1'],
            'carBlocks.*.car_id' => ['required', 'integer', Rule::exists('cars', 'id')],

            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],

            'daily_price' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],

            'status' => ['required', Rule::in(['new', 'confirmed', 'active', 'closed', 'cancelled', 'overdue'])],
            'notes' => ['nullable', 'string', 'max:5000'],
            'carBlocks.*.use_trusted_person' => ['boolean'],
            'carBlocks.*.trusted_person_name' => ['nullable', 'string', 'max:120'],
            'carBlocks.*.trusted_person_phone' => ['nullable', 'string', 'max:30'],
            'carBlocks.*.trusted_person_license_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function parseDt(?string $value): ?Carbon
    {
        if (!$value) return null;

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function calcDays(Carbon $from, Carbon $to): int
    {
        // считаем “сутки” как минимум 1 день, округляя вверх
        $minutes = max(0, $from->diffInMinutes($to));
        return max(1, (int) ceil($minutes / 1440));
    }

    private function blankCarBlock(): array
    {
        return [
            'car_id' => null,
            'use_trusted_person' => false,
            'trusted_person_name' => null,
            'trusted_person_phone' => null,
            'trusted_person_license_number' => null,
        ];
    }

    public function addCarBlock(): void
    {
        $this->carBlocks[] = $this->blankCarBlock();
        $this->recalc();
    }

    public function removeCarBlock(int $index): void
    {
        unset($this->carBlocks[$index]);
        $this->carBlocks = array_values($this->carBlocks);
        $this->recalc();
    }

    private function selectedCarIds(): array
    {
        return array_values(array_filter(array_map(function ($block) {
            return isset($block['car_id']) ? (int) $block['car_id'] : null;
        }, $this->carBlocks)));
    }

    private function applyMandatoryExtras(): void
    {
        $carCount = max(1, count($this->selectedCarIds()));

        foreach ($this->mandatoryExtraIds as $extraId) {
            $this->selectedExtras[$extraId] = $carCount;
        }
    }

    private function syncPrimaryCarPricing(): void
    {
        if ($this->overridePricing) {
            return;
        }

        $primaryId = $this->carBlocks[0]['car_id'] ?? null;
        if (! $primaryId) {
            return;
        }

        $car = Car::find($primaryId);
        if (! $car) {
            return;
        }

        $this->daily_price = (string) ($car->daily_price ?? '0.00');
        $this->deposit_amount = (string) ($car->deposit_amount ?? '0.00');
    }

    public function recalc(): void
    {
        $this->applyMandatoryExtras();
        $from = $this->parseDt($this->starts_at);
        $to   = $this->parseDt($this->ends_at);

        if (!$from || !$to || $to->lessThanOrEqualTo($from)) {
            $this->days = 0;
            $this->rent_amount = '0.00';
            $this->extras_amount = '0.00';
            $this->total_amount = '0.00';
            return;
        }

        $this->days = $this->calcDays($from, $to);

        $daily   = (float) $this->daily_price;
        $deposit = (float) $this->deposit_amount;

        // базовая аренда
        $rent = 0.0;
        $depositTotal = 0.0;

        $this->rebuildCarSummaries();
        foreach ($this->carSummaries as $summary) {
            $rent += $this->days * (float) $summary['daily_price'];
            $depositTotal += (float) $summary['deposit_amount'];
        }

        // доп. услуги (фикс/за день)
        $extrasTotal = $this->calcExtrasTotal($this->days);

        // итого к оплате: (аренда + услуги) + депозит
        $total = $rent + $extrasTotal + $depositTotal;

        $this->rent_amount   = number_format($rent, 2, '.', '');
        $this->extras_amount = number_format($extrasTotal, 2, '.', '');
        $this->total_amount  = number_format($total, 2, '.', '');
    }

    private function rebuildCarSummaries(): void
    {
        $carIds = $this->selectedCarIds();
        if (empty($carIds)) {
            $this->carSummaries = [];
            return;
        }

        $cars = Car::query()->whereIn('id', $carIds)->get()->keyBy('id');
        $this->carSummaries = [];

        foreach ($carIds as $carId) {
            $car = $cars->get($carId);
            if (!$car) {
                continue;
            }
            $daily = $this->overridePricing ? (float) $this->daily_price : (float) ($car->daily_price ?? 0);
            $deposit = $this->overridePricing ? (float) $this->deposit_amount : (float) ($car->deposit_amount ?? 0);
            $this->carSummaries[] = [
                'id' => $car->id,
                'label' => trim($car->brand.' '.$car->model.' • '.$car->plate_number),
                'daily_price' => $daily,
                'deposit_amount' => $deposit,
            ];
        }
    }


    private function hasOverlap(int $carId, Carbon $from, Carbon $to): bool
    {
        // пересечение: existing.starts < newEnd AND existing.ends > newStart
        return Rental::query()
            ->where('car_id', $carId)
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->exists();
    }

    public function save()
    {
        $data = $this->validate();

        $this->applyMandatoryExtras();
        $blocks = array_values($data['carBlocks'] ?? []);
        $carIds = array_values(array_unique(array_map(fn($block) => (int) $block['car_id'], $blocks)));

        if (count($carIds) !== count($blocks)) {
            $this->addError('carBlocks', 'Один и тот же автомобиль нельзя выбрать дважды.');
            return;
        }

        $primaryCarId = $carIds[0] ?? null;
        if (! $primaryCarId) {
            $this->addError('carBlocks', 'Добавьте хотя бы один автомобиль.');
            return;
        }

        $primaryCar = Car::findOrFail($primaryCarId);

        // базовая защита
        foreach ($carIds as $carId) {
            $car = Car::find($carId);
            if (!$car || (isset($car->is_active) && !$car->is_active)) {
                $this->addError('carBlocks', 'Один из выбранных автомобилей неактивен.');
                return;
            }
        }

        $from = Carbon::parse($data['starts_at']);
        $to   = Carbon::parse($data['ends_at']);

        if ($to->lessThanOrEqualTo($from)) {
            $this->addError('ends_at', 'Дата/время окончания должно быть позже начала.');
            return;
        }

        foreach ($carIds as $carId) {
            if ($this->hasOverlap($carId, $from, $to)) {
                $this->addError('starts_at', 'На этот период уже есть аренда/бронь для выбранного авто.');
                $this->addError('ends_at', 'Выбери другой период или другой автомобиль.');
                return;
            }
        }

        // ✅ цены: либо из авто, либо ручные (если разрешили)
        $daily = $this->overridePricing
            ? (float) ($data['daily_price'] ?? 0)
            : (float) ($primaryCar->daily_price ?? 0);

        $deposit = $this->overridePricing
            ? (float) ($data['deposit_amount'] ?? 0)
            : (float) ($primaryCar->deposit_amount ?? 0);

        if ($daily <= 0) {
            $this->addError('daily_price', 'Не задана цена аренды за день.');
            return;
        }

        $days = $this->calcDays($from, $to);        // как у тебя уже сделано
        $days = max(1, (int) $days);                // страховка

        $extrasTotal = $this->calcExtrasTotal($days);

        // создаём аренду
        $groupUuid = (string) Str::uuid();
        $primaryRental = null;

        foreach ($carIds as $index => $carId) {
            $car = Car::findOrFail($carId);
            $carDaily = $this->overridePricing ? $daily : (float) ($car->daily_price ?? 0);
            $carDeposit = $this->overridePricing ? $deposit : (float) ($car->deposit_amount ?? 0);

            $carRentTotal = round($days * $carDaily, 2);
            $carExtrasTotal = $index === 0 ? $extrasTotal : 0;
            $carGrandTotal = round($carRentTotal + $carExtrasTotal, 2);

            $block = $blocks[$index] ?? [];
            $useTrusted = (bool) ($block['use_trusted_person'] ?? false);
            $trustedName = $useTrusted ? trim((string) ($block['trusted_person_name'] ?? '')) : null;
            $trustedPhone = $useTrusted ? trim((string) ($block['trusted_person_phone'] ?? '')) : null;
            $trustedLicense = $useTrusted ? trim((string) ($block['trusted_person_license_number'] ?? '')) : null;

            if ($useTrusted && ($trustedName === '' || $trustedPhone === '' || $trustedLicense === '')) {
                $this->addError('carBlocks', 'Заполните данные доверенного лица для каждого выбранного авто.');
                return;
            }

            $rental = Rental::create([
                'client_id'  => (int) $data['client_id'],
                'car_id'     => $carId,
                'manager_id' => auth()->id(),

                'starts_at' => $from,
                'ends_at'   => $to,

                'status' => $data['status'],
                'notes'  => $data['notes'] ?? null,

                'group_uuid' => $groupUuid,
                'is_trusted_person' => $useTrusted,
                'trusted_person_name' => $trustedName,
                'trusted_person_phone' => $trustedPhone,
                'trusted_person_license_number' => $trustedLicense,

                'daily_price'    => $carDaily,
                'deposit_amount' => round($carDeposit, 2),

                'days_count'     => $days,
                'base_total'     => $carRentTotal,
                'discount_total' => 0,
                'penalty_total'  => 0,
                'grand_total'    => $carGrandTotal,
            ]);

            if ($index === 0) {
                $primaryRental = $rental;
            }
        }

        $sync = [];

        if (!empty($this->selectedExtras)) {
            $ids = array_keys($this->selectedExtras);

            $extras = Extra::query()
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->get();

            foreach ($extras as $extra) {
                $qty = max(1, (int)($this->selectedExtras[$extra->id] ?? 1));

                $sync[$extra->id] = [
                    'pricing_type' => $extra->pricing_type,
                    'price'        => (float) $extra->price,
                    'qty'          => $qty,
                ];
            }
        }

        if (!empty($sync) && $primaryRental) {
            $primaryRental->extras()->sync($sync);
        }



        session()->flash('success', 'Аренда создана.');
        return redirect()->route('manager.rentals.show', $primaryRental);
    }

    public function updatedOverridePricing(): void
    {
        if ($this->overridePricing) {
            $this->rebuildCarSummaries();
            $this->recalc();
            return;
        }

        $this->syncPrimaryCarPricing();
        $this->rebuildCarSummaries();
        $this->recalc();
    }

    public function toggleExtra(int $extraId): void
    {
        if (in_array($extraId, $this->mandatoryExtraIds, true)) {
            $this->selectedExtras[$extraId] = $this->selectedExtras[$extraId] ?? 1;
            return;
        }

        if (isset($this->selectedExtras[$extraId])) {
            unset($this->selectedExtras[$extraId]);
        } else {
            $this->selectedExtras[$extraId] = 1;
        }

        $this->recalc();
    }

    private function calcExtrasTotal(int $days): float
    {
        if (empty($this->selectedExtras) || $days <= 0) {
            return 0.0;
        }

        $ids = array_keys($this->selectedExtras);

        $extras = Extra::query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $sum = 0.0;

        foreach ($this->selectedExtras as $id => $qtyRaw) {
            $extra = $extras->get((int)$id);
            if (!$extra) continue;

            $qty = max(1, (int)$qtyRaw);
            $unit = (float) $extra->price;

            $line = ($extra->pricing_type === 'per_day')
                ? $unit * $qty * $days
                : $unit * $qty;

            $sum += $line;
        }

        return round($sum, 2);
    }





    public function render()
    {
        // пока просто списки (норм для старта)
        $clients = Client::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(300)
            ->get();

        $from = $this->parseDt($this->starts_at);
        $to = $this->parseDt($this->ends_at);

        if ($from && $to && $to->lessThanOrEqualTo($from)) {
            $to = $from->copy()->addDay();
        }

        if ($from && !$to) {
            $to = $from->copy()->addDay();
        }

        if ($to && !$from) {
            $from = $to->copy()->subDay();
        }

        $carsQuery = Car::query()
            ->where('is_active', true)
            ->where('status', 'available');

        if ($from && $to) {
            $carsQuery->whereDoesntHave('rentals', function ($query) use ($from, $to) {
                $query->whereNotIn('status', ['cancelled', 'closed'])
                    ->where('starts_at', '<', $to)
                    ->where('ends_at', '>', $from);
            });
        }

        $cars = $carsQuery
            ->orderBy('brand')
            ->orderBy('model')
            ->limit(300)
            ->get();

        $statuses = [
            'new' => 'Новая',
            'confirmed' => 'Подтверждена',
            'active' => 'Активна',
            'closed' => 'Закрыта',
            'cancelled' => 'Отменена',
            'overdue' => 'Просрочена',
        ];

        return view('livewire.manager.rentals.form', compact('clients', 'cars', 'statuses'));
    }
}
