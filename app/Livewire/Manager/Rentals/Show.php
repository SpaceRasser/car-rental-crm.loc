<?php

namespace App\Livewire\Manager\Rentals;

use App\Models\Payment;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public int $rentalId;

    // UI состояния
    public bool $showPickupForm = false;
    public bool $showReturnForm = false;

    // Выдача
    public array $pickupData = [];

    // Возврат
    public array $returnData = [];

    public function mount(int $rentalId): void
    {
        $this->rentalId = $rentalId;
    }

    public function getRentalProperty(): Rental
    {
        return Rental::with([
            'car',
            'client',
            'extras', // ✅ добавили
            'payments' => fn($q) => $q->orderByDesc('id'),
        ])->findOrFail($this->rentalId);
    }

    private function getGroupRentals(): \Illuminate\Support\Collection
    {
        $rental = $this->rental;

        if ($rental->group_uuid) {
            return Rental::query()
                ->with(['car', 'client', 'extras', 'payments' => fn($q) => $q->orderByDesc('id')])
                ->where('group_uuid', $rental->group_uuid)
                ->orderBy('id')
                ->get();
        }

        return collect([$rental->loadMissing(['car', 'client', 'extras', 'payments'])]);
    }

    private function calcExtrasTotal(Rental $rental, int $days): float
    {
        return (float) $rental->extras->sum(function ($e) use ($days) {
            $type  = (string) ($e->pivot->pricing_type ?? 'fixed');
            $price = (float) ($e->pivot->price ?? 0);
            $qty   = (int) ($e->pivot->qty ?? 1);
            $mult = $type === 'per_day' ? $days : 1;

            return round($price * $qty * $mult, 2);
        });
    }


    // -------------------------
    // Статусы (как было)
    // -------------------------
    public function setStatus(string $status): void
    {
        $rental = $this->rental;
        $groupRentals = $this->getGroupRentals();

        $allowed = ['new', 'confirmed', 'active', 'closed', 'cancelled', 'overdue'];
        if (!in_array($status, $allowed, true)) {
            return;
        }

        $map = [
            'new'       => ['confirmed', 'cancelled'],
            'confirmed' => ['active', 'cancelled'],
            'active'    => ['closed', 'overdue'],
            'overdue'   => ['closed'],
            'closed'    => [],
            'cancelled' => [],
        ];

        foreach ($groupRentals as $item) {
            $current = $item->status;

            if (!in_array($status, $map[$current] ?? [], true)) {
                session()->flash('rental_error', "Нельзя сменить статус с '{$current}' на '{$status}'.");
                return;
            }
        }

        foreach ($groupRentals as $item) {
            $current = $item->status;
            $item->update(['status' => $status]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($item)
                ->event('status_changed')
                ->withProperties(['from' => $current, 'to' => $status])
                ->log("Статус аренды #{$item->id}: {$current} → {$status}");

            if ($item->car) {
                if ($status === 'active') {
                    $item->car->update(['status' => 'rented']);
                }
                if (in_array($status, ['closed', 'cancelled'], true)) {
                    $item->car->update(['status' => 'available']);
                }
            }
        }

        $rental->refresh();
        $this->dispatch('$refresh');
        session()->flash('rental_success', 'Статус обновлён.');
    }

    // -------------------------
    // Выдача / Возврат
    // -------------------------
    public function openPickup(): void
    {
        $this->rental->refresh();
        $groupRentals = $this->getGroupRentals();
        $notConfirmed = $groupRentals->first(fn($item) => $item->status !== 'confirmed');

        if ($notConfirmed) {
            session()->flash('rental_error', 'Выдача доступна только для статуса "Подтверждена".');
            return;
        }

        $this->resetValidation();
        $this->showReturnForm = false;
        $this->showPickupForm = true;

        $this->pickupData = [];
        foreach ($groupRentals as $item) {
            $this->pickupData[$item->id] = [
                'mileage_start_km' => $item->mileage_start_km,
                'fuel_start_percent' => $item->fuel_start_percent,
            ];
        }
    }

    public function cancelPickup(): void
    {
        $this->resetValidation();
        $this->showPickupForm = false;
    }

    public function confirmPickup(): void
    {
        $this->rental->refresh();
        $groupRentals = $this->getGroupRentals();
        $notConfirmed = $groupRentals->first(fn($item) => $item->status !== 'confirmed');

        if ($notConfirmed) {
            session()->flash('rental_error', 'Выдача доступна только для статуса "Подтверждена".');
            return;
        }

        $data = $this->validate([
            'pickupData.*.mileage_start_km' => ['required', 'integer', 'min:0'],
            'pickupData.*.fuel_start_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ], [], [
            'pickupData.*.mileage_start_km' => 'Пробег при выдаче',
            'pickupData.*.fuel_start_percent' => 'Топливо при выдаче',
        ]);

        foreach ($groupRentals as $item) {
            $pickup = $data['pickupData'][$item->id] ?? null;
            if (! $pickup) {
                continue;
            }

            $item->update([
                'picked_up_at' => now(),
                'mileage_start_km' => (int) $pickup['mileage_start_km'],
                'fuel_start_percent' => (int) $pickup['fuel_start_percent'],
                'status' => 'active',
            ]);

            if ($item->car) {
                $item->car->update(['status' => 'rented']);
            }
        }

        $this->showPickupForm = false;

        $this->dispatch('$refresh');
        session()->flash('rental_success', 'Авто выдано. Аренда активирована.');
    }

    public function openReturn(): void
    {
        $this->rental->refresh();
        $groupRentals = $this->getGroupRentals();
        $notActive = $groupRentals->first(fn($item) => !in_array($item->status, ['active', 'overdue'], true));

        if ($notActive) {
            session()->flash('rental_error', 'Возврат доступен только для "Активна/Просрочена".');
            return;
        }

        $this->resetValidation();
        $this->showPickupForm = false;
        $this->showReturnForm = true;

        $this->returnData = [];
        foreach ($groupRentals as $item) {
            $this->returnData[$item->id] = [
                'mileage_end_km' => $item->mileage_end_km,
                'fuel_end_percent' => $item->fuel_end_percent,
                'penalty_total' => (string) ($item->penalty_total ?? '0.00'),
            ];
        }
    }

    public function cancelReturn(): void
    {
        $this->resetValidation();
        $this->showReturnForm = false;
    }

    public function confirmReturn(): void
    {
        $this->rental->refresh();
        $groupRentals = $this->getGroupRentals();
        $notActive = $groupRentals->first(fn($item) => !in_array($item->status, ['active', 'overdue'], true));

        if ($notActive) {
            session()->flash('rental_error', 'Возврат доступен только для "Активна/Просрочена".');
            return;
        }

        $data = $this->validate([
            'returnData.*.mileage_end_km' => ['required', 'integer', 'min:0'],
            'returnData.*.fuel_end_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'returnData.*.penalty_total' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'returnData.*.mileage_end_km' => 'Пробег при возврате',
            'returnData.*.fuel_end_percent' => 'Топливо при возврате',
            'returnData.*.penalty_total' => 'Штрафы/доплаты',
        ]);

        foreach ($groupRentals as $item) {
            $return = $data['returnData'][$item->id] ?? null;
            if (! $return) {
                continue;
            }

            $mileageEnd = (int) $return['mileage_end_km'];
            $mileageStart = (int) ($item->mileage_start_km ?? 0);

            if ($item->mileage_start_km !== null && $mileageEnd < $mileageStart) {
                $this->addError('returnData.'.$item->id.'.mileage_end_km', 'Пробег при возврате не может быть меньше пробега при выдаче.');
                return;
            }

            $penalty = round((float) ($return['penalty_total'] ?? 0), 2);
            $days = max(1, (int) ($item->days_count ?? 1));
            $extrasTotal = $this->calcExtrasTotal($item, $days);

            $base = (float) ($item->base_total ?? 0);
            $discount = (float) ($item->discount_total ?? 0);
            $grand = round(max(0, $base + $extrasTotal - $discount + $penalty), 2);

            $item->update([
                'returned_at' => now(),
                'mileage_end_km' => $mileageEnd,
                'fuel_end_percent' => (int) $return['fuel_end_percent'],
                'penalty_total' => $penalty,
                'grand_total' => $grand,
                'status' => 'closed',
            ]);

            if ($item->car) {
                $item->car->update(['status' => 'available']);
            }
        }

        $this->showReturnForm = false;

        $this->dispatch('$refresh');
        session()->flash('rental_success', 'Аренда закрыта. Возврат зафиксирован.');
    }

    // -------------------------
    // Платежи (как у тебя уже правильно)
    // -------------------------
    public function createPayment(): void
    {
        $this->rental->refresh();
        $groupRentals = $this->getGroupRentals();
        $primaryRental = $groupRentals->first();

        // ✅ дни: берем слепок, иначе считаем как в Form (ceil по суткам)
        $days = (int) ($primaryRental?->days_count ?? 0);
        if ($days <= 0 && $primaryRental?->starts_at && $primaryRental?->ends_at) {
            $from = \Carbon\Carbon::parse($primaryRental->starts_at);
            $to   = \Carbon\Carbon::parse($primaryRental->ends_at);

            $minutes = max(1, $from->diffInMinutes($to));
            $days = max(1, (int) ceil($minutes / 1440));
        }
        if ($days <= 0) $days = 1;

        $base     = (float) $groupRentals->sum(fn($item) => (float) ($item->base_total ?? 0));
        $discount = (float) $groupRentals->sum(fn($item) => (float) ($item->discount_total ?? 0));
        $penalty  = (float) $groupRentals->sum(fn($item) => (float) ($item->penalty_total ?? 0));
        $deposit  = (float) $groupRentals->sum(fn($item) => (float) ($item->deposit_amount ?? 0));

        // ✅ услуги из pivot rental_extras (fixed/per_day, qty)
        $extrasTotal = (float) ($primaryRental?->extras ?? collect())->sum(function ($e) use ($days) {
            $type  = (string) ($e->pivot->pricing_type ?? 'fixed');
            $price = (float) ($e->pivot->price ?? 0);
            $qty   = (int)   ($e->pivot->qty ?? 1);

            $mult = $type === 'per_day' ? $days : 1;

            return round($price * $qty * $mult, 2);
        });

        // ✅ аренда итого (без депозита)
        $rentTotal = round(max(0, $base + $extrasTotal - $discount + $penalty), 2);

        // ✅ всего к оплате (с депозитом)
        $total = round($rentTotal + $deposit, 2);

        // ✅ сколько уже реально оплачено
        $paid = (float) $groupRentals
            ->flatMap(fn ($item) => $item->payments)
            ->where('status', 'paid')
            ->sum('amount');

        $remaining = round(max(0, $total - $paid), 2);

        if ($remaining <= 0) {
            session()->flash('payment_error', 'Эта аренда уже полностью оплачена.');
            return;
        }

        \App\Models\Payment::create([
            'rental_id' => $primaryRental?->id ?? $this->rentalId,
            'amount' => $remaining,
            'currency' => 'RUB',
            'provider' => 'fake',
            'status' => 'pending',

            // ✅ обязательное поле в твоей БД
            'payment_reference' => 'INV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999),

            'external_id' => null,
            'paid_at' => null,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('$refresh');
        session()->flash('payment_success', 'Платёж создан (pending).');
    }


    public function simulateSuccess(int $paymentId): void
    {
        $payment = Payment::where('rental_id', $this->rentalId)->findOrFail($paymentId);

        if ($payment->status === 'paid') {
            return;
        }

        $payment->update([
            'status'            => 'paid',
            'external_id'       => 'fake_' . Str::uuid(),
            'payment_reference' => $payment->payment_reference ?: ('PAY-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)),
            'paid_at'           => now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->event('payment_paid')
            ->withProperties(['rental_id' => $payment->rental_id, 'amount' => $payment->amount])
            ->log("Платёж #{$payment->id} помечен как PAID");


        $groupRentals = $this->getGroupRentals();
        foreach ($groupRentals as $item) {
            if ($item->status === 'new') {
                $item->update(['status' => 'confirmed']);
            }
        }

        $this->dispatch('$refresh');
        session()->flash('payment_success', 'Оплата успешно симулирована.');
    }

    public function simulateFail(int $paymentId): void
    {
        $payment = Payment::where('rental_id', $this->rentalId)->findOrFail($paymentId);

        if ($payment->status === 'paid') {
            session()->flash('payment_error', 'Нельзя “провалить” уже оплаченный платёж.');
            return;
        }

        $payment->update([
            'status'            => 'failed',
            'external_id'       => 'fake_' . Str::uuid(),
            'payment_reference' => $payment->payment_reference ?: ('FAIL-' . now()->format('YmdHis') . '-' . random_int(1000, 9999)),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($payment)
            ->event('payment_failed')
            ->withProperties(['rental_id' => $payment->rental_id, 'amount' => $payment->amount])
            ->log("Платёж #{$payment->id} помечен как FAILED");


        $this->dispatch('$refresh');
        session()->flash('payment_success', 'Платёж помечен как failed.');
    }

    public function render()
    {
        $rental = $this->rental;
        $groupRentals = $this->getGroupRentals();
        $primaryRental = $groupRentals->first();

        // ✅ дни: берем слепок, иначе считаем как в Form (ceil по суткам)
        $days = (int) ($primaryRental?->days_count ?? 0);
        if ($days <= 0 && $primaryRental?->starts_at && $primaryRental?->ends_at) {
            $from = \Carbon\Carbon::parse($primaryRental->starts_at);
            $to   = \Carbon\Carbon::parse($primaryRental->ends_at);

            $minutes = max(1, $from->diffInMinutes($to));
            $days = max(1, (int) ceil($minutes / 1440));
        }
        if ($days <= 0) $days = 1;

        $base = (float) $groupRentals->sum(fn($item) => (float) ($item->base_total ?? 0));
        $discount = (float) $groupRentals->sum(fn($item) => (float) ($item->discount_total ?? 0));
        $penalty  = (float) $groupRentals->sum(fn($item) => (float) ($item->penalty_total ?? 0));

        // ✅ строки услуг + сумма услуг
        $extrasLines = ($primaryRental?->extras ?? collect())->map(function ($e) use ($days) {
            $type = (string) ($e->pivot->pricing_type ?? 'fixed'); // fixed/per_day
            $price = (float) ($e->pivot->price ?? 0);
            $qty = (int) ($e->pivot->qty ?? 1);
            $mult = $type === 'per_day' ? $days : 1;

            $amount = round($price * $qty * $mult, 2);

            return (object) [
                'id' => $e->id,
                'name' => $e->name,
                'pricing_type' => $type,
                'price' => $price,
                'qty' => $qty,
                'days' => $days,
                'amount' => $amount,
            ];
        })->values();

        $extrasTotal = round((float) $extrasLines->sum('amount'), 2);

        // ✅ аренда итого (база + услуги - скидка + штрафы)
        $rentTotal = round(max(0, $base + $extrasTotal - $discount + $penalty), 2);

        $deposit = (float) $groupRentals->sum(fn($item) => (float) ($item->deposit_amount ?? 0));
        $total = round($rentTotal + $deposit, 2);

        $paid = (float) $groupRentals
            ->flatMap(fn($item) => $item->payments)
            ->where('status', 'paid')
            ->sum('amount');
        $remaining = round(max(0, $total - $paid), 2);

        $statusLabels = [
            'new' => 'Новая',
            'confirmed' => 'Подтверждена',
            'active' => 'Активна',
            'closed' => 'Закрыта',
            'cancelled' => 'Отменена',
            'overdue' => 'Просрочена',
        ];

        return view('livewire.manager.rentals.show', compact(
            'rental',
            'groupRentals',
            'days',
            'base',
            'extrasLines',
            'extrasTotal',
            'discount',
            'penalty',
            'rentTotal',
            'deposit',
            'paid',
            'total',
            'remaining',
            'statusLabels'
        ));
    }

}
